from flask import Flask, jsonify, request
import mysql.connector
from collections import defaultdict, deque
import os

app = Flask(__name__)

DB_CONFIG = {
    "host": os.getenv("MYSQL_HOST", "127.0.0.1"),
    "port": int(os.getenv("MYSQL_PORT", "3306")),
    "user": os.getenv("MYSQL_USER", "root"),
    "password": os.getenv("MYSQL_PASSWORD", ""),
    "database": os.getenv("MYSQL_DATABASE", "criminal_intelligence_demo"),
}


def get_db():
    return mysql.connector.connect(**DB_CONFIG)


def get_columns(conn, table):
    cursor = conn.cursor()
    cursor.execute("""
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = %s
        ORDER BY ORDINAL_POSITION
    """, (table,))

    columns = [row[0] for row in cursor.fetchall()]
    cursor.close()

    return columns


def find_column(columns, possible_names):

    lower = {c.lower(): c for c in columns}

    for name in possible_names:
        if name.lower() in lower:
            return lower[name.lower()]

    return None


def get_person(conn, search):

    columns = get_columns(conn, "persons")

    id_column = find_column(
        columns,
        ["person_id", "id"]
    )

    name_column = find_column(
        columns,
        ["full_name", "name", "person_name"]
    )

    cursor = conn.cursor(dictionary=True)

    # Search by ID
    if str(search).isdigit() and id_column:

        cursor.execute(
            f"""
            SELECT *
            FROM persons
            WHERE `{id_column}` = %s
            LIMIT 1
            """,
            (int(search),)
        )

    # Search by name
    elif name_column:

        cursor.execute(
            f"""
            SELECT *
            FROM persons
            WHERE `{name_column}` LIKE %s
            LIMIT 1
            """,
            (f"%{search}%",)
        )

    else:
        cursor.close()
        return None

    person = cursor.fetchone()

    cursor.close()

    return person


# ---------------------------------------------------------
# Build relationships through shared entities
# ---------------------------------------------------------

def build_shared_relationships(
    conn,
    relationship_table,
    entity_column
):

    relationships = []

    if not table_exists(conn, relationship_table):
        return relationships

    columns = get_columns(conn, relationship_table)

    person_column = find_column(
        columns,
        ["person_id"]
    )

    entity_col = find_column(
        columns,
        [entity_column]
    )

    if not person_column or not entity_col:
        return relationships

    cursor = conn.cursor(dictionary=True)

    cursor.execute(
        f"""
        SELECT `{person_column}` AS person_id,
               `{entity_col}` AS entity_id
        FROM `{relationship_table}`
        WHERE `{person_column}` IS NOT NULL
        AND `{entity_col}` IS NOT NULL
        """
    )

    rows = cursor.fetchall()

    cursor.close()

    grouped = defaultdict(list)

    for row in rows:
        grouped[row["entity_id"]].append(
            row["person_id"]
        )

    for entity_id, people in grouped.items():

        people = list(set(people))

        for i in range(len(people)):

            for j in range(i + 1, len(people)):

                relationships.append({
                    "person_a": people[i],
                    "person_b": people[j],
                    "type": relationship_table,
                    "entity_id": entity_id,
                    "weight": 2
                })

    return relationships


def table_exists(conn, table):

    cursor = conn.cursor()

    cursor.execute("""
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = %s
    """, (table,))

    result = cursor.fetchone()[0]

    cursor.close()

    return result > 0


# ---------------------------------------------------------
# Build complete person graph
# ---------------------------------------------------------

def build_graph(conn):

    relationships = []

    # Person ↔ Phone
    relationships += build_shared_relationships(
        conn,
        "person_phone",
        "phone_id"
    )

    # Person ↔ Address
    relationships += build_shared_relationships(
        conn,
        "person_address",
        "address_id"
    )

    # Person ↔ Organization
    relationships += build_shared_relationships(
        conn,
        "person_organization",
        "organization_id"
    )

    # Person ↔ Vehicle
    relationships += build_shared_relationships(
        conn,
        "vehicle_owner",
        "vehicle_id"
    )

    # Person ↔ Case
    relationships += build_shared_relationships(
        conn,
        "case_person",
        "case_id"
    )

    return relationships


# ---------------------------------------------------------
# Network discovery
# ---------------------------------------------------------

def discover_network(conn, target_person, depth=2):

    relationships = build_graph(conn)

    graph = defaultdict(list)

    for relationship in relationships:

        a = int(relationship["person_a"])
        b = int(relationship["person_b"])

        graph[a].append(
            (b, relationship)
        )

        graph[b].append(
            (a, relationship)
        )

    target = int(target_person)

    visited = {
        target: 0
    }

    queue = deque([
        target
    ])

    discovered_relationships = []

    while queue:

        current = queue.popleft()

        current_depth = visited[current]

        if current_depth >= depth:
            continue

        for neighbour, relationship in graph[current]:

            discovered_relationships.append(
                relationship
            )

            if neighbour not in visited:

                visited[neighbour] = (
                    current_depth + 1
                )

                queue.append(neighbour)

    return list(visited.keys()), discovered_relationships


# ---------------------------------------------------------
# Get person names
# ---------------------------------------------------------

def get_person_names(conn):

    columns = get_columns(conn, "persons")

    id_column = find_column(
        columns,
        ["person_id", "id"]
    )

    name_column = find_column(
        columns,
        ["full_name", "name", "person_name"]
    )

    cursor = conn.cursor(dictionary=True)

    if name_column:

        cursor.execute(
            f"""
            SELECT `{id_column}` AS person_id,
                   `{name_column}` AS name
            FROM persons
            """
        )

    else:

        cursor.execute(
            f"""
            SELECT `{id_column}` AS person_id
            FROM persons
            """
        )

    rows = cursor.fetchall()

    cursor.close()

    return {
        int(row["person_id"]):
            row.get("name") or f"Person {row['person_id']}"
        for row in rows
    }


# ---------------------------------------------------------
# API
# ---------------------------------------------------------

@app.route("/api/health")
def health():

    try:

        conn = get_db()

        connected = conn.is_connected()

        conn.close()

        return jsonify({
            "status": "ok",
            "mysql": connected
        })

    except Exception as error:

        return jsonify({
            "status": "error",
            "message": str(error)
        }), 500


@app.route("/api/network")
def network():

    search = request.args.get(
        "person",
        ""
    ).strip()

    if not search:

        return jsonify({
            "error":
            "Enter a person ID or name."
        }), 400

    try:

        conn = get_db()

        person = get_person(
            conn,
            search
        )

        if not person:

            conn.close()

            return jsonify({
                "error":
                "Person not found."
            }), 404

        columns = get_columns(
            conn,
            "persons"
        )

        id_column = find_column(
            columns,
            ["person_id", "id"]
        )

        name_column = find_column(
            columns,
            ["full_name", "name", "person_name"]
        )

        target_id = int(
            person[id_column]
        )

        target_name = (
            person[name_column]
            if name_column
            else f"Person {target_id}"
        )

        depth = int(
            request.args.get(
                "depth",
                "2"
            )
        )

        depth = max(
            1,
            min(depth, 4)
        )

        people, relationships = discover_network(
            conn,
            target_id,
            depth
        )

        names = get_person_names(
            conn
        )

        nodes = []

        for person_id in people:

            nodes.append({
                "person_id": person_id,
                "name": names.get(
                    person_id,
                    f"Person {person_id}"
                ),
                "is_target":
                    person_id == target_id
            })

        links = []

        seen = set()

        for relationship in relationships:

            a = int(
                relationship["person_a"]
            )

            b = int(
                relationship["person_b"]
            )

            key = tuple(
                sorted([a, b])
            )

            if key in seen:
                continue

            seen.add(key)

            links.append({
                "source": a,
                "target": b,
                "relationship":
                    relationship["type"],
                "entity_id":
                    relationship["entity_id"],
                "weight":
                    relationship["weight"]
            })

        result = {
            "target": {
                "person_id":
                    target_id,
                "name":
                    target_name
            },

            "depth":
                depth,

            "network_size":
                len(nodes),

            "connection_count":
                len(links),

            "nodes":
                nodes,

            "links":
                links
        }

        conn.close()

        return jsonify(result)

    except Exception as error:

        print(
            "\nNETWORK ERROR:",
            error
        )

        return jsonify({
            "error":
                str(error)
        }), 500


if __name__ == "__main__":

    app.run(
        host="127.0.0.1",
        port=5000,
        debug=True
    )

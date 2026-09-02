<?php

$apiBase = "http://127.0.0.1:5000";

$query = trim($_GET["person"] ?? "");

$data = null;
$error = "";

if ($query !== "") {

    $url =
        $apiBase .
        "/api/network?person=" .
        urlencode($query) .
        "&depth=2";

    $context = stream_context_create([
        "http" => [
            "timeout" => 10
        ]
    ]);

    $response = @file_get_contents($url, false, $context);

    if ($response === false) {

        $error =
            "Cannot connect to Flask API. Make sure flask_app.py is running on port 5000.";

    } else {

        $data = json_decode($response, true);

        if (!$data) {

            $error = "Flask returned invalid JSON.";

        } elseif (isset($data["error"])) {

            $error = $data["error"];
        }
    }
}

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
Criminal Intelligence Network
</title>


<!-- D3 -->

<script src="https://cdn.jsdelivr.net/npm/d3@7"></script>


<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    background: #f1f5f9;

    color: #0f172a;

    font-family:
        Arial,
        Helvetica,
        sans-serif;
}


/* HEADER */

.header {

    background: #111827;

    color: white;

    padding: 22px 35px;

}

.header h1 {

    margin: 0;

    font-size: 25px;

}


/* MAIN */

.container {

    width: 96%;

    max-width: 1600px;

    margin: 25px auto;

}


/* SEARCH */

.search-panel {

    background: white;

    padding: 20px;

    border-radius: 12px;

    border: 1px solid #dbe2ea;

    box-shadow:
        0 2px 8px rgba(0,0,0,0.04);

}


.search-form {

    display: flex;

    gap: 10px;

}


.search-form input {

    flex: 1;

    height: 52px;

    padding: 0 16px;

    font-size: 17px;

    border: 1px solid #cbd5e1;

    border-radius: 8px;

}


.search-form button {

    height: 52px;

    padding: 0 25px;

    border: none;

    border-radius: 8px;

    background: #2563eb;

    color: white;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;

}


.search-form button:hover {

    background: #1d4ed8;

}


/* ERROR */

.error {

    margin-top: 20px;

    padding: 15px;

    background: #fee2e2;

    color: #991b1b;

    border-radius: 8px;

}


/* STATISTICS */

.stats {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 15px;

    margin-top: 20px;

}


.stat {

    background: white;

    border: 1px solid #dbe2ea;

    border-radius: 12px;

    padding: 20px;

}


.stat-number {

    font-size: 30px;

    font-weight: bold;

    margin-bottom: 5px;

}


.stat-label {

    font-size: 15px;

    color: #475569;

}


/* GRAPH PANEL */

.graph-panel {

    margin-top: 20px;

    background: white;

    border: 1px solid #dbe2ea;

    border-radius: 12px;

    overflow: hidden;

}


.graph-header {

    padding: 15px 20px;

    border-bottom: 1px solid #e2e8f0;

    display: flex;

    justify-content: space-between;

    align-items: center;

}


.graph-title {

    font-size: 18px;

    font-weight: bold;

}


.graph-controls {

    display: flex;

    gap: 8px;

}


.graph-controls button {

    border: 1px solid #cbd5e1;

    background: white;

    border-radius: 6px;

    padding: 7px 12px;

    cursor: pointer;

}


.graph-controls button:hover {

    background: #f1f5f9;

}


/* GRAPH */

#graph {

    width: 100%;

    height: 720px;

    position: relative;

    overflow: hidden;

    background:
        radial-gradient(
            circle at center,
            #ffffff 0%,
            #f8fafc 100%
        );

}


/* SVG */

#network-svg {

    width: 100%;

    height: 100%;

    display: block;

}


/* EDGES */

.network-link {

    stroke: #94a3b8;

    stroke-opacity: 0.55;

}


.network-link.strong {

    stroke: #ef4444;

    stroke-opacity: 0.8;

}


/* EDGE LABEL */

.edge-label {

    font-size: 10px;

    fill: #64748b;

    pointer-events: none;

}


/* NODE */

.node {

    cursor: pointer;

}


.node-circle {

    stroke: white;

    stroke-width: 3px;

}


.node-target {

    fill: #dc2626;

}


.node-normal {

    fill: #2563eb;

}


.node:hover .node-circle {

    stroke: #f59e0b;

    stroke-width: 5px;

}


/* NODE LABEL */

.node-label {

    font-size: 12px;

    font-weight: 600;

    fill: #1e293b;

    pointer-events: none;

}


/* LEGEND */

.legend {

    padding: 15px 20px;

    border-top: 1px solid #e2e8f0;

    display: flex;

    gap: 25px;

    align-items: center;

}


.legend-item {

    display: flex;

    align-items: center;

    gap: 8px;

    font-size: 13px;

}


.legend-circle {

    width: 13px;

    height: 13px;

    border-radius: 50%;

}


.legend-target {

    background: #dc2626;

}


.legend-normal {

    background: #2563eb;

}


/* CONNECTION TABLE */

.connections-panel {

    margin-top: 20px;

    background: white;

    border: 1px solid #dbe2ea;

    border-radius: 12px;

    overflow: hidden;

}


.connections-panel h2 {

    margin: 0;

    padding: 18px 20px;

    border-bottom: 1px solid #e2e8f0;

}


table {

    width: 100%;

    border-collapse: collapse;

}


th,
td {

    padding: 12px 15px;

    text-align: left;

    border-bottom: 1px solid #e2e8f0;

}


th {

    background: #f8fafc;

    font-size: 13px;

}


td {

    font-size: 14px;

}


.badge {

    padding: 4px 8px;

    border-radius: 5px;

    background: #eff6ff;

    color: #1d4ed8;

    font-size: 12px;

}


/* MOBILE */

@media(max-width: 800px) {

    .stats {

        grid-template-columns: 1fr;

    }

    .search-form {

        flex-direction: column;

    }

    #graph {

        height: 600px;

    }

}

</style>

</head>


<body>


<div class="header">

    <h1>
        Criminal Intelligence — Network Analysis
    </h1>

</div>


<div class="container">


    <!-- SEARCH -->

    <div class="search-panel">

        <form
            class="search-form"
            method="GET"
        >

            <input
                type="text"
                name="person"
                placeholder="Enter person name or person ID..."
                value="<?= htmlspecialchars($query) ?>"
                required
            >

            <button type="submit">
                Analyze Network
            </button>

        </form>

    </div>


<?php if ($error): ?>

    <div class="error">

        <?= htmlspecialchars($error) ?>

    </div>

<?php endif; ?>


<?php if ($data): ?>


    <!-- STATISTICS -->

    <div class="stats">


        <div class="stat">

            <div class="stat-number">

                <?= count($data["nodes"]) ?>

            </div>

            <div class="stat-label">

                People in Network

            </div>

        </div>


        <div class="stat">

            <div class="stat-number">

                <?= count($data["links"]) ?>

            </div>

            <div class="stat-label">

                Connections

            </div>

        </div>


        <div class="stat">

            <div class="stat-number">

                <?= htmlspecialchars(
                    $data["target"]["name"] ?? "Unknown"
                ) ?>

            </div>

            <div class="stat-label">

                Target Person

            </div>

        </div>


    </div>



    <!-- GRAPH -->

    <div class="graph-panel">


        <div class="graph-header">


            <div class="graph-title">

                Network Graph

            </div>


            <div class="graph-controls">

                <button id="zoomIn">
                    +
                </button>

                <button id="zoomOut">
                    −
                </button>

                <button id="resetZoom">
                    Reset
                </button>

                <button id="recenter">
                    Recenter
                </button>

            </div>


        </div>


        <div id="graph">

            <svg id="network-svg"></svg>

        </div>


        <div class="legend">


            <div class="legend-item">

                <div class="legend-circle legend-target"></div>

                Target Person

            </div>


            <div class="legend-item">

                <div class="legend-circle legend-normal"></div>

                Connected Person

            </div>


            <div>

                Mouse wheel = Zoom

            </div>


            <div>

                Drag = Move network

            </div>


        </div>


    </div>



    <!-- CONNECTION TABLE -->

    <div class="connections-panel">


        <h2>
            Detected Connections
        </h2>


        <table>


            <thead>

                <tr>

                    <th>
                        Person A
                    </th>

                    <th>
                        Person B
                    </th>

                    <th>
                        Relationship
                    </th>

                    <th>
                        Entity
                    </th>

                    <th>
                        Strength
                    </th>

                </tr>

            </thead>


            <tbody>


<?php foreach ($data["links"] as $link): ?>


                <tr>


                    <td>

                        <?= htmlspecialchars(
                            $link["source"] ?? ""
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $link["target"] ?? ""
                        ) ?>

                    </td>


                    <td>

                        <span class="badge">

                            <?= htmlspecialchars(
                                $link["relationship"] ?? "Connection"
                            ) ?>

                        </span>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $link["entity_id"] ?? "-"
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $link["weight"] ?? "1"
                        ) ?>

                    </td>


                </tr>


<?php endforeach; ?>


            </tbody>

        </table>

    </div>


<?php endif; ?>


</div>



<?php if ($data): ?>

<script>


/* =====================================================
   DATA FROM FLASK
===================================================== */

const networkData =
    <?= json_encode(
        $data,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ) ?>;


/* =====================================================
   GRAPH CONTAINER
===================================================== */

const graphContainer =
    document.getElementById("graph");

const svg =
    d3.select("#network-svg");


/* =====================================================
   DIMENSIONS
===================================================== */

let width =
    graphContainer.clientWidth;

let height =
    graphContainer.clientHeight;


/* =====================================================
   SVG VIEW
===================================================== */

svg
    .attr("width", width)
    .attr("height", height)
    .attr(
        "viewBox",
        `0 0 ${width} ${height}`
    );


/* =====================================================
   MAIN ZOOM GROUP
===================================================== */

const zoomGroup =
    svg.append("g")
        .attr("class", "zoom-group");


/* =====================================================
   NORMALIZE DATA
===================================================== */

const nodes =
    networkData.nodes.map(
        d => ({
            ...d,
            id: String(d.person_id)
        })
    );


const links =
    networkData.links.map(
        d => ({
            ...d,
            source: String(d.source),
            target: String(d.target)
        })
    );


/* =====================================================
   ZOOM
===================================================== */

const zoom =
    d3.zoom()
        .scaleExtent([0.15, 5])
        .on(
            "zoom",
            event => {

                zoomGroup.attr(
                    "transform",
                    event.transform
                );

            }
        );


svg.call(zoom);


/* =====================================================
   LINKS
===================================================== */

const linkGroup =
    zoomGroup
        .append("g")
        .attr("class", "links");


const link =
    linkGroup
        .selectAll("line")
        .data(links)
        .join("line")
        .attr("class", d =>
            d.weight >= 3
                ? "network-link strong"
                : "network-link"
        )
        .attr(
            "stroke-width",
            d =>
                Math.max(
                    1,
                    Math.min(
                        7,
                        Number(d.weight || 1)
                    )
                )
        );


/* =====================================================
   EDGE LABELS
===================================================== */

const edgeLabels =
    zoomGroup
        .append("g")
        .attr("class", "edge-labels")
        .selectAll("text")
        .data(links)
        .join("text")
        .attr("class", "edge-label")
        .text(
            d =>
                d.relationship
                    ? d.relationship.replaceAll("_", " ")
                    : ""
        );


/* =====================================================
   NODES
===================================================== */

const nodeGroup =
    zoomGroup
        .append("g")
        .attr("class", "nodes");


const node =
    nodeGroup
        .selectAll("g")
        .data(nodes)
        .join("g")
        .attr("class", "node")
        .call(
            d3.drag()
                .on(
                    "start",
                    dragStarted
                )
                .on(
                    "drag",
                    dragged
                )
                .on(
                    "end",
                    dragEnded
                )
        );


/* =====================================================
   NODE CIRCLE
===================================================== */

node
    .append("circle")
    .attr(
        "class",
        d =>
            "node-circle " +
            (
                d.is_target
                    ? "node-target"
                    : "node-normal"
            )
    )
    .attr(
        "r",
        d =>
            d.is_target
                ? 23
                : 15
    );


/* =====================================================
   NODE LABEL
===================================================== */

node
    .append("text")
    .attr(
        "class",
        "node-label"
    )
    .attr(
        "dx",
        20
    )
    .attr(
        "dy",
        4
    )
    .text(
        d =>
            d.name
                ? d.name
                : "Person " + d.person_id
    );


/* =====================================================
   NODE TOOLTIP
===================================================== */

node
    .append("title")
    .text(
        d =>
            `${d.name || "Person"}\nPerson ID: ${d.person_id}`
    );


/* =====================================================
   FORCE SIMULATION
===================================================== */

const simulation =
    d3.forceSimulation(nodes)

        .force(
            "link",
            d3.forceLink(links)
                .id(d => d.id)
                .distance(170)
                .strength(0.45)
        )

        .force(
            "charge",
            d3.forceManyBody()
                .strength(-600)
        )

        .force(
            "center",
            d3.forceCenter(
                width / 2,
                height / 2
            )
        )

        .force(
            "collision",
            d3.forceCollide()
                .radius(
                    d =>
                        d.is_target
                            ? 45
                            : 32
                )
                .strength(1)
        )

        .force(
            "x",
            d3.forceX(
                width / 2
            ).strength(0.05)
        )

        .force(
            "y",
            d3.forceY(
                height / 2
            ).strength(0.05)
        );


/* =====================================================
   TICK
===================================================== */

simulation.on(
    "tick",
    () => {


        /* Keep nodes inside graph */

        nodes.forEach(
            d => {

                d.x =
                    Math.max(
                        40,
                        Math.min(
                            width - 40,
                            d.x
                        )
                    );

                d.y =
                    Math.max(
                        40,
                        Math.min(
                            height - 40,
                            d.y
                        )
                    );

            }
        );


        /* Links */

        link
            .attr(
                "x1",
                d => d.source.x
            )
            .attr(
                "y1",
                d => d.source.y
            )
            .attr(
                "x2",
                d => d.target.x
            )
            .attr(
                "y2",
                d => d.target.y
            );


        /* Edge labels */

        edgeLabels
            .attr(
                "x",
                d =>
                    (
                        d.source.x +
                        d.target.x
                    ) / 2
            )
            .attr(
                "y",
                d =>
                    (
                        d.source.y +
                        d.target.y
                    ) / 2
            );


        /* Nodes */

        node.attr(
            "transform",
            d =>
                `translate(${d.x},${d.y})`
        );

    }
);


/* =====================================================
   DRAG
===================================================== */

function dragStarted(event, d) {

    if (!event.active) {

        simulation.alphaTarget(
            0.3
        ).restart();

    }

    d.fx = d.x;
    d.fy = d.y;

}


function dragged(event, d) {

    d.fx = event.x;
    d.fy = event.y;

}


function dragEnded(event, d) {

    if (!event.active) {

        simulation.alphaTarget(0);

    }

    d.fx = null;
    d.fy = null;

}


/* =====================================================
   ZOOM CONTROLS
===================================================== */

document
    .getElementById("zoomIn")
    .onclick = () => {

        svg.transition()
            .duration(300)
            .call(
                zoom.scaleBy,
                1.4
            );

    };


document
    .getElementById("zoomOut")
    .onclick = () => {

        svg.transition()
            .duration(300)
            .call(
                zoom.scaleBy,
                0.7
            );

    };


document
    .getElementById("resetZoom")
    .onclick = () => {

        svg.transition()
            .duration(400)
            .call(
                zoom.transform,
                d3.zoomIdentity
            );

    };


/* =====================================================
   RECENTER
===================================================== */

document
    .getElementById("recenter")
    .onclick = () => {

        simulation
            .alpha(0.5)
            .restart();

        svg.transition()
            .duration(400)
            .call(
                zoom.transform,
                d3.zoomIdentity
            );

    };


/* =====================================================
   RESIZE
===================================================== */

window.addEventListener(
    "resize",
    () => {

        width =
            graphContainer.clientWidth;

        height =
            graphContainer.clientHeight;

        svg
            .attr(
                "width",
                width
            )
            .attr(
                "height",
                height
            )
            .attr(
                "viewBox",
                `0 0 ${width} ${height}`
            );

        simulation
            .force(
                "center",
                d3.forceCenter(
                    width / 2,
                    height / 2
                )
            );

        simulation
            .alpha(0.3)
            .restart();

    }
);


</script>

<?php endif; ?>


</body>

</html>
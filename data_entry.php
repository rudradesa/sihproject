<?php
require_once "db.php";

/*
 * Generic data-entry CRUD for the CURRENT criminal_intelligence_demo database.
 * The page reads column metadata from INFORMATION_SCHEMA, so forms stay aligned
 * with the actual table structure.
 */

$allowedTables = [
    "persons",
    "addresses",
    "organizations",
    "cases",
    "vehicles",
    "phones",
    "cdr",
    "transactions",
    "surveillance_events",
    "social_interactions",
    "intelligence_reports"
];

$table = $_GET["table"] ?? "";
if (!in_array($table, $allowedTables, true)) {
    http_response_code(404);
    die("Invalid table.");
}

function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function labelize(string $name): string {
    return ucwords(str_replace("_", " ", $name));
}

/* Get actual columns from the existing database. */
$columnStmt = $pdo->prepare("
    SELECT
        COLUMN_NAME,
        DATA_TYPE,
        COLUMN_TYPE,
        IS_NULLABLE,
        COLUMN_DEFAULT,
        EXTRA,
        ORDINAL_POSITION
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = :table
    ORDER BY ORDINAL_POSITION
");
$columnStmt->execute([":table" => $table]);
$columns = $columnStmt->fetchAll();

if (!$columns) {
    die("Table not found or contains no columns.");
}

/* Get foreign-key targets where the current schema actually defines them. */
$fkStmt = $pdo->prepare("
    SELECT
        kcu.COLUMN_NAME,
        kcu.REFERENCED_TABLE_NAME,
        kcu.REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
    WHERE kcu.TABLE_SCHEMA = DATABASE()
      AND kcu.TABLE_NAME = :table
      AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
");
$fkStmt->execute([":table" => $table]);

$foreignKeys = [];
foreach ($fkStmt->fetchAll() as $fk) {
    $foreignKeys[$fk["COLUMN_NAME"]] = $fk;
}

/* Load select options for foreign keys. */
$fkOptions = [];
foreach ($foreignKeys as $column => $fk) {
    $refTable = $fk["REFERENCED_TABLE_NAME"];
    $refColumn = $fk["REFERENCED_COLUMN_NAME"];

    // A conservative whitelist prevents metadata from becoming executable SQL.
    if (!preg_match('/^[A-Za-z0-9_]+$/', $refTable) ||
        !preg_match('/^[A-Za-z0-9_]+$/', $refColumn)) {
        continue;
    }

    $q = $pdo->query("
        SELECT `$refColumn` AS id, *
        FROM `$refTable`
        ORDER BY `$refColumn` DESC
        LIMIT 500
    ");

    $rows = $q->fetchAll();
    $fkOptions[$column] = [
        "table" => $refTable,
        "column" => $refColumn,
        "rows" => $rows
    ];
}

$message = "";
$error = "";

/* INSERT */
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "insert") {

    $insertColumns = [];
    $placeholders = [];
    $params = [];

    foreach ($columns as $col) {
        $name = $col["COLUMN_NAME"];

        // Let MySQL generate auto-increment IDs/default values.
        if (stripos($col["EXTRA"], "auto_increment") !== false) {
            continue;
        }

        if (!array_key_exists($name, $_POST)) {
            continue;
        }

        $value = $_POST[$name];

        // Empty optional values become NULL.
        if ($value === "" && $col["IS_NULLABLE"] === "YES") {
            $value = null;
        }

        $insertColumns[] = "`$name`";
        $placeholders[] = ":p_" . preg_replace('/[^A-Za-z0-9_]/', '_', $name);
        $params[":p_" . preg_replace('/[^A-Za-z0-9_]/', '_', $name)] = $value;
    }

    try {
        if (!$insertColumns) {
            throw new Exception("No insertable fields were submitted.");
        }

        $sql = "INSERT INTO `$table` (" .
               implode(", ", $insertColumns) .
               ") VALUES (" .
               implode(", ", $placeholders) .
               ")";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $message = "Record added successfully.";
    } catch (Throwable $e) {
        $error = "Insert failed: " . $e->getMessage();
    }
}

/* Read latest records */
$primaryKey = null;
foreach ($columns as $col) {
    // Prefer a column named *_id or id for the row display.
    if (strcasecmp($col["COLUMN_NAME"], "id") === 0 ||
        preg_match('/(^|_)id$/i', $col["COLUMN_NAME"])) {
        $primaryKey = $col["COLUMN_NAME"];
        break;
    }
}

if (!$primaryKey) {
    $primaryKey = $columns[0]["COLUMN_NAME"];
}

$listStmt = $pdo->query("SELECT * FROM `$table` ORDER BY `$primaryKey` DESC LIMIT 100");
$rows = $listStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h(labelize($table)) ?> | Criminal Intelligence</title>
<style>
*{box-sizing:border-box}
body{margin:0;background:#f5f7fb;color:#172033;font-family:Arial,Helvetica,sans-serif}
.header{background:#111827;color:#fff;padding:20px 5%}
.header-inner{max-width:1250px;margin:auto}
.header a{color:#cbd5e1;text-decoration:none;font-size:13px}
.header h1{margin:8px 0 0;font-size:24px}
.container{max-width:1250px;margin:auto;padding:30px 5%}
.card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;margin-bottom:24px;box-shadow:0 7px 22px rgba(15,23,42,.06)}
h2{margin:0 0 18px;font-size:19px}
.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px}
.field label{display:block;font-size:13px;font-weight:700;margin-bottom:7px}
.field input,.field select,.field textarea{width:100%;padding:10px 11px;border:1px solid #d1d5db;border-radius:7px;font-size:14px;background:#fff}
.field textarea{min-height:90px;resize:vertical}
.field small{display:block;color:#6b7280;margin-top:5px;font-size:11px}
.actions{margin-top:20px}
button{border:0;border-radius:7px;padding:11px 18px;background:#1d4ed8;color:#fff;font-weight:700;cursor:pointer}
button:hover{background:#173ea5}
.message{padding:12px;border-radius:7px;margin-bottom:18px;background:#dcfce7;color:#166534}
.error{padding:12px;border-radius:7px;margin-bottom:18px;background:#fee2e2;color:#991b1b}
.table-wrap{overflow:auto}
table{border-collapse:collapse;width:100%;min-width:700px}
th,td{border-bottom:1px solid #e5e7eb;padding:10px;text-align:left;font-size:12px;white-space:nowrap}
th{background:#111827;color:#fff;position:sticky;top:0}
.muted{color:#6b7280;font-size:13px}
.back{display:inline-block;margin-top:18px;color:#1d4ed8;text-decoration:none}
@media(max-width:700px){.form-grid{grid-template-columns:1fr}}
</style>
</head>
<body>

<header class="header">
<div class="header-inner">
<a href="index.php">← Dashboard</a>
<h1><?= h(labelize($table)) ?></h1>
</div>
</header>

<main class="container">

<div class="card">
<h2>Add <?= h(labelize($table)) ?></h2>
<p class="muted">
This form is generated from the current MySQL table structure.
</p>

<?php if ($message): ?><div class="message"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>

<form method="post">
<input type="hidden" name="action" value="insert">

<div class="form-grid">

<?php foreach ($columns as $col):
    $name = $col["COLUMN_NAME"];
    $extra = strtolower($col["EXTRA"]);

    if (strpos($extra, "auto_increment") !== false) continue;

    $required = ($col["IS_NULLABLE"] === "NO" && $col["COLUMN_DEFAULT"] === null);
    $type = strtolower($col["DATA_TYPE"]);
    $value = $_POST[$name] ?? "";
?>
<div class="field">
<label for="<?= h($name) ?>">
    <?= h(labelize($name)) ?><?= $required ? " *" : "" ?>
</label>

<?php if (isset($fkOptions[$name])): ?>

<select id="<?= h($name) ?>" name="<?= h($name) ?>" <?= $required ? "required" : "" ?>>
    <option value="">-- Select --</option>
    <?php foreach ($fkOptions[$name]["rows"] as $option):
        $display = $option["id"];
        // Prefer a human-readable name when the referenced table has one.
        foreach (["full_name","name","title","case_number","phone_number","registration_number"] as $candidate) {
            if (array_key_exists($candidate, $option) && $option[$candidate] !== null && $option[$candidate] !== "") {
                $display = $option["id"] . " — " . $option[$candidate];
                break;
            }
        }
    ?>
        <option value="<?= h($option["id"]) ?>" <?= ((string)$value === (string)$option["id"]) ? "selected" : "" ?>>
            <?= h($display) ?>
        </option>
    <?php endforeach; ?>
</select>
<small>References <?= h($fkOptions[$name]["table"]) ?>.<?= h($fkOptions[$name]["column"]) ?></small>

<?php elseif (strpos($type, "text") !== false || strpos($type, "blob") !== false): ?>

<textarea id="<?= h($name) ?>" name="<?= h($name) ?>" <?= $required ? "required" : "" ?>><?= h($value) ?></textarea>

<?php elseif (strpos($type, "date") !== false && $type === "date"): ?>

<input type="date" id="<?= h($name) ?>" name="<?= h($name) ?>" value="<?= h($value) ?>" <?= $required ? "required" : "" ?>>

<?php elseif (strpos($type, "datetime") !== false || strpos($type, "timestamp") !== false): ?>

<input type="datetime-local" id="<?= h($name) ?>" name="<?= h($name) ?>" value="<?= h(str_replace(" ","T",$value)) ?>" <?= $required ? "required" : "" ?>>

<?php elseif ($type === "time"): ?>

<input type="time" id="<?= h($name) ?>" name="<?= h($name) ?>" value="<?= h($value) ?>" <?= $required ? "required" : "" ?>>

<?php elseif (strpos($col["COLUMN_TYPE"], "enum(") === 0): ?>

<select id="<?= h($name) ?>" name="<?= h($name) ?>" <?= $required ? "required" : "" ?>>
    <option value="">-- Select --</option>
    <?php
    preg_match_all("/'([^']*)'/", $col["COLUMN_TYPE"], $matches);
    foreach ($matches[1] as $enumValue):
    ?>
        <option value="<?= h($enumValue) ?>" <?= $value === $enumValue ? "selected" : "" ?>>
            <?= h($enumValue) ?>
        </option>
    <?php endforeach; ?>
</select>

<?php else: ?>

<input
    type="<?= in_array($type, ["int","integer","bigint","smallint","mediumint","tinyint","decimal","float","double"]) ? "number" : "text" ?>"
    id="<?= h($name) ?>"
    name="<?= h($name) ?>"
    value="<?= h($value) ?>"
    <?= $required ? "required" : "" ?>
>

<?php endif; ?>
</div>
<?php endforeach; ?>

</div>

<div class="actions">
<button type="submit">Add Record</button>
</div>
</form>
</div>

<div class="card">
<h2>Latest Records</h2>
<p class="muted">Showing up to 100 records from the existing table.</p>

<div class="table-wrap">
<table>
<thead>
<tr>
<?php foreach ($columns as $col): ?>
<th><?= h(labelize($col["COLUMN_NAME"])) ?></th>
<?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php foreach ($rows as $row): ?>
<tr>
<?php foreach ($columns as $col): ?>
<td><?= h($row[$col["COLUMN_NAME"]] ?? "") ?></td>
<?php endforeach; ?>
</tr>
<?php endforeach; ?>
<?php if (!$rows): ?>
<tr><td colspan="<?= count($columns) ?>">No records found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

</main>
</body>
</html>

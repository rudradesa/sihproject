<?php
// index.php
require_once "db.php";

function countRows(PDO $pdo, string $table): int {
    // Table names are hard-coded below; no user input reaches this query.
    return (int) $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}

$cards = [
    ["Persons", "data_entry.php?table=persons", "persons", "People and identity records", "👤"],
    ["Cases", "data_entry.php?table=cases", "cases", "FIR and investigation records", "📁"],
    ["Vehicles", "data_entry.php?table=vehicles", "vehicles", "Vehicle and ownership records", "🚗"],
    ["Phones", "data_entry.php?table=phones", "phones", "Phone and SIM records", "📱"],
    ["Addresses", "data_entry.php?table=addresses", "addresses", "Residential and address records", "📍"],
    ["Organizations", "data_entry.php?table=organizations", "organizations", "Organization and role records", "🏢"],
    ["CDR", "data_entry.php?table=cdr", "cdr", "Call detail records", "📞"],
    ["Transactions", "data_entry.php?table=transactions", "transactions", "Financial transaction records", "💳"],
    ["Surveillance", "data_entry.php?table=surveillance_events", "surveillance_events", "CCTV / ANPR observations", "🎥"],
    ["Social Intelligence", "data_entry.php?table=social_interactions", "social_interactions", "Social interactions", "🌐"],
    ["Intelligence Reports", "data_entry.php?table=intelligence_reports", "intelligence_reports", "Field and intelligence reports", "📄"],
];

$dbError = null;
try {
    $stats = [];
    foreach ($cards as $card) {
        $stats[$card[0]] = countRows($pdo, $card[2]);
    }
} catch (PDOException $e) {
    $dbError = $e->getMessage();
    $stats = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Criminal Intelligence System</title>

<style>
:root {
    --bg: #f5f7fb;
    --card: #ffffff;
    --text: #172033;
    --muted: #6b7280;
    --border: #e5e7eb;
    --primary: #1d4ed8;
    --primary-dark: #173ea5;
    --shadow: 0 8px 25px rgba(15, 23, 42, .07);
}

* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: var(--bg);
    color: var(--text);
}

.header {
    background: #111827;
    color: white;
    padding: 24px 5%;
}

.header-inner {
    max-width: 1250px;
    margin: auto;
}

.brand {
    font-size: 25px;
    font-weight: 700;
    letter-spacing: .2px;
}

.subtitle {
    margin-top: 7px;
    color: #cbd5e1;
    font-size: 14px;
}

.container {
    max-width: 1250px;
    margin: 0 auto;
    padding: 35px 5%;
}

.section-title {
    margin: 0 0 5px;
    font-size: 22px;
}

.section-description {
    margin: 0 0 25px;
    color: var(--muted);
}

.error {
    background: #fee2e2;
    color: #991b1b;
    padding: 14px 16px;
    border-radius: 8px;
    margin-bottom: 22px;
}

.grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.card {
    display: flex;
    align-items: center;
    gap: 17px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    text-decoration: none;
    color: inherit;
    box-shadow: var(--shadow);
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
}

.card:hover {
    transform: translateY(-2px);
    border-color: #bfdbfe;
    box-shadow: 0 12px 30px rgba(15, 23, 42, .10);
}

.icon {
    width: 50px;
    height: 50px;
    display: grid;
    place-items: center;
    border-radius: 10px;
    background: #eff6ff;
    font-size: 24px;
    flex-shrink: 0;
}

.card-content {
    min-width: 0;
}

.card-title {
    font-size: 17px;
    font-weight: 700;
}

.card-description {
    margin-top: 5px;
    color: var(--muted);
    font-size: 13px;
}

.count {
    margin-left: auto;
    font-size: 20px;
    font-weight: 700;
    color: var(--primary);
}

.analysis {
    margin-top: 30px;
    background: #111827;
    color: white;
    border-radius: 12px;
    padding: 24px;
}

.analysis h2 {
    margin: 0 0 8px;
    font-size: 20px;
}

.analysis p {
    margin: 0 0 17px;
    color: #cbd5e1;
    font-size: 14px;
}

.analysis-button {
    display: inline-block;
    background: white;
    color: #111827;
    padding: 11px 17px;
    border-radius: 7px;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
}

.footer {
    text-align: center;
    color: #9ca3af;
    font-size: 12px;
    padding: 20px;
}

@media (max-width: 700px) {
    .grid {
        grid-template-columns: 1fr;
    }

    .container {
        padding: 25px 4%;
    }
}
</style>
</head>

<body>

<header class="header">
    <div class="header-inner">
        <div class="brand">Criminal Intelligence System</div>
        <div class="subtitle">
            Centralized crime, intelligence and relationship data management
        </div>
    </div>
</header>

<main class="container">

    <?php if ($dbError): ?>
        <div class="error">
            <strong>Database connection/query error:</strong>
            <?= htmlspecialchars($dbError) ?>
        </div>
    <?php endif; ?>

    <h1 class="section-title">Data Management</h1>
    <p class="section-description">
        Select a data source to view or enter records.
    </p>

    <div class="grid">

        <?php foreach ($cards as $card): ?>

            <a class="card" href="<?= htmlspecialchars($card[1]) ?>">

                <div class="icon">
                    <?= $card[4] ?>
                </div>

                <div class="card-content">
                    <div class="card-title">
                        <?= htmlspecialchars($card[0]) ?>
                    </div>

                    <div class="card-description">
                        <?= htmlspecialchars($card[3]) ?>
                    </div>
                </div>

                <?php if (!$dbError): ?>
                    <div class="count">
                        <?= number_format($stats[$card[0]]) ?>
                    </div>
                <?php endif; ?>

            </a>

        <?php endforeach; ?>

    </div>

    <section class="analysis">

        <h2>Network Analysis</h2>

        <p>
            Analyze relationships across cases, communications, financial
            transactions, vehicles, locations and other intelligence sources.
        </p>

        <a class="analysis-button" href="network_analysis.php">
            Open Network Analysis
        </a>

    </section>

</main>

<footer class="footer">
    Criminal Intelligence System · Development Version
</footer>

</body>
</html>

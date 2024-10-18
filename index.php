<?php

// Constants
$history_file = "webpage_history.json";
$current_members_file = "current_members.json";
$staff_count_file = "staff_count.json";

// Function to load the current team members from the file
function load_current_members() {
    global $current_members_file;
    if (file_exists($current_members_file)) {
        return json_decode(file_get_contents($current_members_file), true);
    }
    return [];
}

// Function to load the history from the history file
function load_history() {
    global $history_file;
    if (file_exists($history_file)) {
        return json_decode(file_get_contents($history_file), true);
    }
    return [];
}

// Function to load the staff count over time
function load_staff_count() {
    global $staff_count_file;
    if (file_exists($staff_count_file)) {
        return json_decode(file_get_contents($staff_count_file), true);
    }
    return [];
}

// Function to log the current staff count
function log_staff_count($count) {
    global $staff_count_file;
    $staff_count = load_staff_count();
    $staff_count[date("Y-m-d H:i:s")] = $count;
    file_put_contents($staff_count_file, json_encode($staff_count, JSON_PRETTY_PRINT));
}

// Load current members, history, and staff count
$current_members = load_current_members();
$history = load_history();
$staff_count = load_staff_count();

// Separate members who are currently on the page and those who are no longer on the page
$former_members = [];
foreach ($history as $name => $details) {
    if ($details['currently_on_page'] === false) {
        $former_members[$name] = $details;
    }
}

// Ensure Linus and Yvonne Ho are always at the beginning of the current members
usort($current_members, function ($a, $b) {
    $priority_names = ["Linus", "Yvonne Ho"];
    $a_priority = in_array($a['name'], $priority_names) ? 0 : 1;
    $b_priority = in_array($b['name'], $priority_names) ? 0 : 1;
    return $a_priority - $b_priority;
});

// Log the current staff count
log_staff_count(count($current_members));

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Members Overview</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 20px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .member {
            border: 1px solid #ccc;
            padding: 15px;
            background-color: #fff;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .member img {
            max-width: 100%;
            height: auto;
            border-radius: 50%;
        }
        .timeline, .chart-container {
            margin-bottom: 30px;
            padding: 15px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .timeline h2, .chart-container h2 {
            text-align: center;
        }
    </style>
</head>
<body>

<div class="chart-container">
    <h2>Total Staff Members Over Time</h2>
    <canvas id="staffChart"></canvas>
</div>

<script>
    const ctx = document.getElementById('staffChart').getContext('2d');
    const staffData = <?php echo json_encode($staff_count); ?>;
    const labels = Object.keys(staffData);
    const data = Object.values(staffData);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Number of Staff Members',
                data: data,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true,
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    type: 'time',
                    time: {
                        unit: 'day'
                    }
                },
                y: {
                    beginAtZero: true,
                    precision: 0
                }
            }
        }
    });
</script>

<div class="former-members">
    <h2>Former Team Members</h2>
    <?php if (empty($former_members)) : ?>
        <p>No Former Team Members Detected Yet</p>
    <?php else : ?>
        <?php foreach ($former_members as $name => $details) : ?>
            <div class="member">
                <img src="<?php echo htmlspecialchars($details['image']); ?>" alt="<?php echo htmlspecialchars($name); ?>">
                <h3><?php echo htmlspecialchars($name); ?></h3>
                <p><?php echo htmlspecialchars($details['role']); ?></p>
                <p>First Seen: <?php echo htmlspecialchars($details['first_seen']); ?></p>
                <p>Last Seen: <?php echo htmlspecialchars($details['last_seen']); ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="timeline">
    <h2>Current Team Members</h2>
</div>

<div class="grid">
    <?php foreach ($current_members as $member) : ?>
        <div class="member">
            <img src="<?php echo htmlspecialchars($member['image']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>">
            <h3><?php echo htmlspecialchars($member['name']); ?></h3>
            <p><?php echo htmlspecialchars($member['role']); ?></p>
            <p>First Seen: <?php echo htmlspecialchars($member['first_seen']); ?></p>
            <p>Last Seen: <?php echo $member['last_seen'] ? htmlspecialchars($member['last_seen']) : "Present"; ?></p>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>

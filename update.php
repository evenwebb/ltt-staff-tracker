<?php

// Constants
$url = "https://linusmediagroup.com/our-team"; // URL to monitor
$log_file = "webpage_log.txt";
$history_file = "webpage_history.json";
$current_members_file = "current_members.json";

// Function to get the HTML content of the webpage
function get_webpage_html($url) {
    $options = [
        "http" => [
            "header" => "User-Agent: PHP"
        ]
    ];
    $context = stream_context_create($options);
    $html_content = file_get_contents($url, false, $context);
    if ($html_content === FALSE) {
        throw new Exception("Error fetching the webpage.");
    }

    // Debug: Check if we successfully retrieved the content
    if (strpos($html_content, "<html") !== false) {
        echo "Successfully fetched the webpage.\n";
    } else {
        echo "Warning: The fetched content does not seem to contain valid HTML.\n";
    }

    return $html_content;
}

// Function to extract specific parts of the content to monitor
function extract_relevant_content($html_content) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html_content);
    $xpath = new DOMXPath($dom);
    $team_members = [];

    $figures = $xpath->query("//figure[contains(@class, 'image-block-outer-wrapper')]");
    echo "Number of figure elements found: " . $figures->length . "\n"; // Debug: Log number of elements found

    foreach ($figures as $figure) {
        $name_tag = $xpath->query(".//div[contains(@class, 'image-title sqs-dynamic-text')]/p", $figure);
        $role_tag = $xpath->query(".//div[contains(@class, 'image-subtitle sqs-dynamic-text')]/p", $figure);
        $image_tag = $xpath->query(".//img", $figure);
        
        if ($name_tag->length > 0 && $role_tag->length > 0 && $image_tag->length > 0) {
            $name = trim($name_tag->item(0)->nodeValue);
            $role = trim($role_tag->item(0)->nodeValue);
            $image = $image_tag->item(0)->getAttribute('src');
            $team_members[] = ["name" => $name, "role" => $role, "image" => $image];
        } else {
            echo "Warning: Could not find name, role, or image for a team member.\n";
        }
    }
    usort($team_members, function ($a, $b) {
        return strcmp($a['name'], $b['name']);
    });

    echo "Final extracted content:\n";
    print_r($team_members); // Debug: Output final extracted content
    
    return $team_members;
}

// Function to compare old and new content (only log additions and removals)
function compare_content($old_content, $new_content) {
    $old_members = array_column($old_content, 'name');
    $new_members = array_column($new_content, 'name');

    $additions = array_diff($new_members, $old_members);
    $removals = array_diff($old_members, $new_members);

    $changes = [];
    if (!empty($additions)) {
        $changes[] = "--- New Members Added ---";
        foreach ($additions as $member_name) {
            $changes[] = "+ $member_name";
        }
    }
    if (!empty($removals)) {
        $changes[] = "--- Members Removed ---";
        foreach ($removals as $member_name) {
            $changes[] = "- $member_name";
        }
    }
    return $changes;
}

// Function to log changes to the history file
function log_changes($changes, $new_content) {
    global $history_file;
    $history = file_exists($history_file) ? json_decode(file_get_contents($history_file), true) : [];

    foreach ($changes as $change) {
        if (substr($change, 0, 2) == "+ ") {
            $name = substr($change, 2);
            foreach ($new_content as $member) {
                if ($member['name'] == $name) {
                    $history[$name] = [
                        "role" => $member['role'],
                        "image" => $member['image'],
                        "first_seen" => date("Y-m-d H:i:s"),
                        "last_seen" => null,
                        "currently_on_page" => true
                    ];
                }
            }
        } elseif (substr($change, 0, 2) == "- ") {
            $name = substr($change, 2);
            if (isset($history[$name])) {
                $history[$name]['last_seen'] = date("Y-m-d H:i:s");
                $history[$name]['currently_on_page'] = false;
            }
        }
    }

    // Update the status for members still on the page
    foreach ($new_content as $member) {
        if (isset($history[$member['name']])) {
            $history[$member['name']]['currently_on_page'] = true;
        }
    }

    file_put_contents($history_file, json_encode($history, JSON_PRETTY_PRINT));

    // Debug: Log if history was successfully updated
    echo "Updated history: \n";
    print_r($history);
}

// Function to load the previous content from the history file
function load_previous_content() {
    global $history_file;
    if (file_exists($history_file)) {
        $history = json_decode(file_get_contents($history_file), true);
        $team_members = [];
        foreach ($history as $name => $details) {
            if ($details['currently_on_page'] === true) {
                $team_members[] = [
                    "name" => $name,
                    "role" => $details['role'],
                    "image" => $details['image'],
                    "first_seen" => $details['first_seen'],
                    "last_seen" => $details['last_seen'],
                    "currently_on_page" => $details['currently_on_page']
                ];
            }
        }
        return $team_members;
    }
    return [];
}

// Main function to check for changes
function check_for_changes() {
    global $url, $history_file, $current_members_file;
    try {
        $new_html_content = get_webpage_html($url);
        $new_content = extract_relevant_content($new_html_content);
        $old_content = load_previous_content();

        if (!empty($old_content) || !empty($new_content)) {
            $changes = compare_content($old_content, $new_content);
            if (!empty($changes)) {
                echo "Changes detected! Logging changes...\n";
                log_changes($changes, $new_content);
            } else {
                echo "No changes detected.\n";
            }
        } else {
            echo "No previous content found. Saving current content...\n";
            log_changes([], $new_content);
        }

        // Load the updated history
        $history = json_decode(file_get_contents($history_file), true);

        // Add full details to current_members.json
        $current_members = [];
        foreach ($history as $name => $details) {
            if ($details['currently_on_page'] === true) {
                $current_members[] = [
                    "name" => $name,
                    "role" => $details['role'],
                    "image" => $details['image'],
                    "first_seen" => $details['first_seen'],
                    "last_seen" => $details['last_seen'],
                    "currently_on_page" => $details['currently_on_page']
                ];
            }
        }

        file_put_contents($current_members_file, json_encode($current_members, JSON_PRETTY_PRINT));

        // Debug: Log if current members were successfully saved
        echo "Current members saved to JSON: \n";
        print_r($current_members);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

// Run the script once
check_for_changes();

echo "Script executed successfully.\n";
?>

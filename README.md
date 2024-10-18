# LTT Team Members Log

## Overview

This PHP-based project is designed to monitor changes in the team members listed on a specific webpage. The application periodically scrapes the webpage to detect updates such as additions or removals of team members. It logs these changes over time, providing a detailed history, including when a team member was first seen, and when they were last seen. The current and former members are displayed in a user-friendly interface, along with a chart that shows the number of staff members over time.

## Features

- **Web Scraping**: Periodically scrapes the specified webpage to detect team member changes.
- **Logging and History Tracking**: Logs each team member's first seen date, last seen date, and current status.
- **Chart Visualization**: Displays a chart of the total staff count over time using Chart.js.
- **Former and Current Members Overview**: Lists current team members, with a dedicated section for former team members.
- **Prioritization**: Ensures Linus and Yvonne Ho are always shown at the top of the current members list.

## Technologies Used

- **PHP**: Server-side language for scraping the webpage, logging, and rendering the interface.
- **JavaScript (Chart.js)**: For displaying the historical data of staff count over time.
- **HTML/CSS**: For structuring and styling the interface.

## Installation

1. **Clone the Repository**:
   ```sh
   git clone https://github.com/evenwebb/ltt-staff-tracker.git
   cd team-monitoring-system
   ```

2. **Set Up Environment**:
   - Ensure you have PHP installed (PHP 7.4 or later recommended).
   - Set up a web server such as Apache or Nginx to serve the PHP files.

3. **Configure the Project**:
   - Update the `$url` variable in `scraper.php` with the URL of the webpage you want to monitor.

4. **Run the Script**:
   - You can run the scraper manually by executing the following command:
     ```sh
     php scraper.php
     ```
   - Optionally, set up a cron job to automate the scraping process.

5. **Access the Dashboard**:
   - Navigate to `display.php` in your browser to view the current and former team members along with the staff count chart.

## Usage

- **Manual Scraping**: Run `php scraper.php` to fetch and log the latest team member data.
- **Automated Scraping**: Use a cron job to execute the scraper at regular intervals.
- **Dashboard**: Open `display.php` to view the list of current and former team members, along with the staff count chart.

## File Structure

- `scraper.php`: The main scraper script that fetches and logs team member data.
- `index.php`: The user interface that displays the current and former members, as well as the chart of staff count over time.
- `webpage_history.json`: Stores the history of all team members, including first and last seen dates.
- `current_members.json`: Stores details of the current team members.
- `staff_count.json`: Logs the staff count over time for chart visualization.

## Example Output

- **Current Team Members**: Displays all current members, including their name, role, and image.
- **Former Team Members**: Lists all members who have been removed, along with relevant information such as when they were last seen.
- **Staff Count Chart**: A line chart showing the change in the number of team members over time.

## Contributing

1. Fork the repository.
2. Create a new branch (`git checkout -b feature-branch`).
3. Commit your changes (`git commit -m 'Add a new feature'`).
4. Push to the branch (`git push origin feature-branch`).
5. Open a pull request.

## License

This project is licensed under the MIT License.


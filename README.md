# Lebanon_Vibes
🌲 Lebanon Vibes
A Scalable Community Portal for Localized Business & Event Discovery.
Lebanon Vibes is a full-stack PHP application designed to solve the challenge of content reliability in community directories. By implementing a Strict Administrative Review Pipeline and integrating Live Environmental Data (APIs), the platform provides users with verified and actionable information.

🚀 Technical Core & Workflow
🛡️ Managed Content Lifecycle (Admin-in-the-Loop)
To ensure the platform remains free of spam, I implemented a custom Review-and-Publish workflow:

Asynchronous Submission: Users submit data which is stored in a pending state by default.
Granular Admin Control: A secure administrative layer allows for the validation, editing, or rejection of pending entries.
Conditional Rendering: The front-end engine only displays entries with an approved status.

🌐 Real-Time Data Synergy
Weather Integration: Uses the OpenWeather API to provide live atmospheric data for event venues.
Geospatial Mapping: Utilizes OpenStreetMap to provide interactive navigation points for the end-user.

🛠 Tech Stack & Skills
Backend: PHP 8.x (Modular logic, Session handling, Security)
Frontend: Responsive HTML5, CSS3, JavaScript (ES6)
API Consumption: RESTful API integration (JSON parsing)

📂 Project Structure
/admin - Administrative dashboard & moderation logic.
/api - Service layer for Weather & Map integrations.
/assets - Custom styles and UI assets.
/includes - Reusable UI components.
index.php - Main application entry point.

⚙️ Setup
Clone the project: git clone https://github.com/shatha4203/Lebanon_Vibes.git
Configuration: Add your API keys to the configuration files in the root directory.
Deployment: Move to your local server (XAMPP/WAMP) and access via localhost

















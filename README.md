# Moving Past XAMPP: Docker Setup & AI-Driven Development Workflows

When collaborating on a team web project, the classic *"it works on my machine"* excuse is the ultimate momentum killer. Traditional local stacks like XAMPP often introduce version mismatches and environment friction. 

To solve this for our latest project, **Pickleball-v2**, we shifted entirely to an automated environment using Docker and designed an AI-augmented workspace configuration to build the application incrementally.

---

## Part 1: Replacing XAMPP with Docker Compose

Using Docker allows anyone on the team to spin up the exact same PHP, MySQL, and phpMyAdmin versions without manual configuration.

### 1. The Environment Blueprint
Create a `docker-compose.yml` file in your root directory:

```yaml
name: pickleball-v2

services:
  app:
    image: php:8.2-apache
    restart: unless-stopped
    ports:
      - "${APP_PORT:-8080}:80"
    volumes:
      - ./app:/var/www/html
    environment:
      DB_HOST: db
      DB_PORT: 3306
      DB_NAME: ${MYSQL_DATABASE:-pickleball}
      DB_USER: ${MYSQL_USER:-pickleball}
      DB_PASS: ${MYSQL_PASSWORD:-pickleball_pw}
    command: sh -c "docker-php-ext-install mysqli && apache2-foreground"
    depends_on:
      db:
        condition: service_healthy
    networks:
      - pickleball_net

  db:
    image: mysql:8.0
    restart: unless-stopped
    command:
      - --default-authentication-plugin=caching_sha2_password
      - --character-set-server=utf8mb4
      - --collation-server=utf8mb4_unicode_ci
    environment:
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD:-root_pw}
      MYSQL_DATABASE: ${MYSQL_DATABASE:-pickleball}
      MYSQL_USER: ${MYSQL_USER:-pickleball}
      MYSQL_PASSWORD: ${MYSQL_PASSWORD:-pickleball_pw}
    ports:
      - "${DB_PORT:-3307}:3306"
    volumes:
      - mysql_data:/var/lib/mysql
      - ./db/init:/docker-entrypoint-initdb.d:ro
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-uroot", "-p${MYSQL_ROOT_PASSWORD:-root_pw}"]
      interval: 5s
      timeout: 5s
      retries: 20
      start_period: 20s
    networks:
      - pickleball_net

  phpmyadmin:
    image: phpmyadmin:5
    restart: unless-stopped
    ports:
      - "${PMA_PORT:-8081}:80"
    environment:
      PMA_HOST: db
      PMA_PORT: 3306
      UPLOAD_LIMIT: 64M
    depends_on:
      db:
        condition: service_healthy
    networks:
      - pickleball_net

volumes:
  mysql_data:

networks:
  pickleball_net:
    driver: bridge
```
### 2. Spinning Up the Environment
Run the following command in your terminal to build and start the containers in detached mode:

```bash
docker compose up -d --build
```
Application URL: http://localhost:8080

phpMyAdmin URL: http://localhost:8081


⚠️ Common Gotcha: If you encounter a 403 Forbidden error when hitting localhost, ensure you have an index.php or index.html file inside your local ./app/ directory.

## Part 2: Lessons in AI-Driven Development
We leveraged an external AI IDE coding assistant to build out features. While highly capable, navigating the constraints of modern commercial LLM tools taught us several critical lessons about token management and system prompt architecture.

The Realities of AI Code Assistants
The Quota Cliff: Many commercial AI platforms reset quotas on a monthly basis rather than daily or hourly. If you burn through your allotment early, development grinds to a halt.

UI Transparency Obstacles: Certain IDE extensions lack clear UX indicators for context window usage percentages or remaining token quotas, making it easy to hit walls unexpectedly.

The 80% Rule: Pushing an LLM to more than 80% of its total context window often degrades its reasoning capabilities. Keeping context lean is essential.

Designing the Brain: Our .ai/ Workspace Folder
To maximize efficiency and keep the AI strictly on the rails without consuming unnecessary tokens, we isolated our instructions into a dedicated .ai/ system folder at the root of the project:

```txt
├── .ai/
│   ├── INSTRUCTIONS.md       # Tech stack rules, operational guardrails, and conventions
│   ├── DATABASE_SCHEMA.md    # The complete EID / database schema in Markdown format
│   ├── DESIGN.md             # UI/UX design specifications and logic layouts
│   ├── REQUIREMENTS.md       # The core project description and rubric
│   └── WORKFLOW.md           # Visual logic flows mapped into markdown pseudocode
└── docs/                     # Visual diagrams, wireframes, and dashboard screenshots
```
The Initialization Prompt
Instead of passing raw files blindly, we feed the AI a structural mapping prompt right at the beginning of a session:

```txt
I have organized my web application project files inside a workspace. 

Inside the `.ai/` folder, you will find our project blueprint (`INSTRUCTIONS.md`), database schema (`DATABASE_SCHEMA.md`), the UI design (`DESIGN.md`), and the application workflow (`WORKFLOW.md`). The environment is controlled via the root `docker-compose.yml` file.

Please read `.ai/INSTRUCTIONS.md` to understand our strict tech stack constraints, database schema, and project rules. 

Acknowledge that you have read and understood these files. Once ready, suggest the best logical starting point (e.g., initializing the database tables or creating the core landing page structure) to build this application incrementally.
By working in small, episodic batches, we validation-test code modifications before the context window becomes oversaturated.
```
---
## Future Roadmap & Technical Questions
Looking back at the initial build phase, there are two key enhancements we are exploring for the next cycle:
- Automated Log Systems: Implementing an AI-driven change log system so the LLM can reference a lightweight file of historical updates without needing to re-read the entire source directory.
- Automated UI Testing: Exploring local Model Context Protocol (MCP) servers integrated with Chrome DevTools to let an AI interactively click, catch script issues (like missing closing brackets in JavaScript blocks), and test buttons natively before code commits.

# AI Development Blueprint & Project Constraints

## 1. Project Overview
[Insert a 2-3 sentence summary of what the web application does based on the professor's instructions]

## 2. Environment & Tech Stack (Strict Constraint)
- **Local Environment:** Docker Compose (XAMPP-equivalent environment ensuring version parity across coworker devices).
- **Backend:** PHP (Standard XAMPP stack configuration)
- **Database:** MySQL
- **Frontend:** Vanilla HTML, CSS, JavaScript (unless your professor permitted a framework)

## 3. Reference Documentation
Before writing or modifying any code in the `src/` directory, you MUST read and strictly adhere to the definitions in the following files:
1. `.ai/REQUIREMENTS.txt` - Core features, grading rubrics, and professor constraints.
2. `.ai/DATABASE_SCHEMA.md` - Exact table structures, primary keys, and relationships. Do not invent columns.
3. `.ai/WORKFLOW.md` - The application logic, user roles, and execution flow.
4. `.ai/DESIGN.md` - The application design system.

## 4. UI & Layout Guidelines
- The user interface must mirror the layout, component placement, and visual hierarchy found in the UI stitch screenshot reference (`docs/UI_stitch_screenshot.png`).
- utilize also the design system from `.ai/design.md` 
- Prioritize clean layout, ease of navigation, and clear user feedback (errors, success states) matching the intended workflow steps.

## 5. Development Instructions for the Agent
- Build incrementally. Focus on one complete workflow step or feature module at a time.
- Ensure all database queries match the exact schema provided.
- Write clean, modular code with comments explaining core logic.
- Verify compatibility with the standard PHP/MySQL environment defined in `docker-compose.yml`.
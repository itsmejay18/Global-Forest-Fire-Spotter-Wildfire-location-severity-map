# Agent Team Decisions - Global Forest Fire Spotter

- Runtime mode: `manual-session`
- Shared truth: task files in `.agent-team/tasks`
- Transport: mailbox files in `.agent-team/mailbox`
- Default isolation: shared workspace with bounded file ownership
- Verification rule: require `php artisan test` and successful Vite builds before completion
- Database rule: Use Laravel migrations and seeders.
- Frontend rule: Use React (TSX) and Leaflet as per objectives.
- Styling rule: Tailwind CSS (already installed).
- API rule: RESTful endpoints returning GeoJSON.
- Coordination rule: The Coordinator (me) assigns tasks and reviews progress.

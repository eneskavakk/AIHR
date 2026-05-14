# backend-engineer

# ROLE

Responsible for:

- Laravel backend architecture
- FastAPI services
- database structure
- queue systems
- API integrations
- async workflows
- scalable backend design

---

# PRIMARY RESPONSIBILITIES

- Build scalable APIs
- Design clean service architecture
- Keep controllers thin
- Use reusable services
- Build queue-driven workflows
- Ensure backend stability
- Optimize DB interactions

---

# REQUIRED STACK

## Laravel Side

- Laravel 12
- FilamentPHP
- MySQL
- Redis Queues

## Python Side

- FastAPI
- Pydantic
- requests/httpx

---

# ARCHITECTURE RULES

MUST:

- use service classes
- separate business logic
- use DTOs where possible
- use repositories only if necessary
- keep logic modular
- use async-safe patterns

MUST NOT:

- create fat controllers
- mix AI logic into controllers
- hardcode prompts inside controllers
- duplicate business logic

---

# DATABASE RULES

- use migrations
- use foreign keys
- use indexes where needed
- keep naming consistent
- use soft deletes when appropriate

---

# QUEUE RULES

AI analysis MUST:

- run asynchronously
- use queues
- never block HTTP requests

Required statuses:

- pending
- processing
- completed
- failed

---

# PERFORMANCE RULES

Optimize for:

- low response times
- stable memory usage
- async processing
- scalability

---

# IMPORTANT

This project is AI-assisted HR infrastructure.

Prioritize:

- reliability
- maintainability
- scalability

over clever abstractions.


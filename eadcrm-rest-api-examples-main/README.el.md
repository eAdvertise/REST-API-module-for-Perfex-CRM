<p>
  <a href="https://www.eadvertise.eu/">
    <img src="assets/eadcrm-rest-api-banner.svg" alt="REST API για το eAD-CRM — συνδέστε το eAD-CRM με AI agents, Zapier, WooCommerce, n8n και εφαρμογές τρίτων">
  </a>
</p>

# eAD-CRM REST API — Παραδείγματα, Postman Collection και αποσπάσματα κώδικα

🌐 [English](README.md) · **Ελληνικά** · [简体中文](README.zh-CN.md) · [Español](README.es.md) · [Português (BR)](README.pt-BR.md) · [Italiano](README.it.md) · [Français](README.fr.md) · [Deutsch](README.de.md) · [Türkçe](README.tr.md) · [Tiếng Việt](README.vi.md) · [ไทย](README.th.md) · [العربية](README.ar.md)

> Έτοιμο προς χρήση **Postman collection**, **παραδείγματα κώδικα** (cURL, PHP, Python, JavaScript) και πλήρης
> **κατάλογος πόρων** για το [REST API module του eAD-CRM](https://www.eadvertise.eu/) —
> ο γρηγορότερος τρόπος να **συνδέσετε το eAD-CRM με AI agents και εφαρμογές τρίτων**.

[![Postman](https://img.shields.io/badge/Postman-Collection-orange?logo=postman&logoColor=white)](postman/eadcrm-rest-api.postman_collection.json)
[![OpenAPI 3.0](https://img.shields.io/badge/OpenAPI-3.0-6ba539?logo=openapiinitiative&logoColor=white)](openapi/eadcrm-rest-api.openapi.json)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![eAD-CRM](https://img.shields.io/badge/eAD-CRM-REST%20API-2c7be5)](https://www.eadvertise.eu/)

Το **eAD-CRM REST API** επιτρέπει ανάγνωση και εγγραφή πελατών, leads, τιμολογίων, προσφορών, έργων,
εργασιών και πολλών ακόμη πόρων μέσω ενός καθαρού HTTP/JSON interface. Είναι ιδανικό για διασυνδέσεις CRM,
automation και custom εφαρμογές. Η έκδοση **v3.0** προσθέτει **MCP server για AI agents**, webhooks παραγωγικού
επιπέδου, έτοιμο polling για **Zapier / Make / n8n**, batch λειτουργίες και εξυπνότερα list endpoints.

- 🧩 **Αποκτήστε το module:** https://www.eadvertise.eu/
- 📖 **Οδηγός API / live documentation:** https://apidoc.eadcrm.eu/
- 🧾 **OpenAPI 3.0 specification:** `GET https://yourdomain.com/api/openapi` ([αντίγραφο αναφοράς](openapi/eadcrm-rest-api.openapi.json))

---

## 🚀 Τι νέο υπάρχει στην έκδοση v3.0

| Δυνατότητα | Endpoint | Περιγραφή |
| --- | --- | --- |
| 🤖 **MCP server** | `POST /api/mcp` | Model Context Protocol (JSON-RPC 2.0) με **148 permission-filtered CRM tools** για Claude Desktop, ChatGPT, Cursor, n8n AI Agent και κάθε MCP client |
| 🪝 **Webhooks 2.0** | `/api/webhooks` | **124 events**, REST διαχείριση, ασύγχρονη παράδοση με retries, προστασία SSRF και αιτήματα υπογεγραμμένα με **HMAC** |
| 🔌 **Automation (polling)** | `/api/zapier/*` | Έτοιμα polling triggers για **Zapier, Make.com, n8n** και κάθε polling-based εργαλείο |
| ⚡ **Batch** | `POST /api/batch` | Έως **50 λειτουργίες** σε ένα αίτημα |
| 📚 **Knowledge Base** | `/api/knowledge_base` | CRUD άρθρων και ομάδων |
| 🗒️ **Notes** | `/api/notes` | Πολυμορφικές σημειώσεις για 12 τύπους οντοτήτων |
| 📄 **Εξυπνότερες λίστες** | κάθε list endpoint | Προαιρετικά `?page=&per_page=`, `?fields=`, `?sort=`, `?created_after=&created_before=` |
| 🛡️ **Ασφαλείς εγγραφές** | κάθε `POST` | `Idempotency-Key`, αγνόηση άγνωστων πεδίων στο `PUT`, headers `X-RateLimit-*` |
| 📐 **OpenAPI 3.0** | `GET /api/openapi` | Ολόκληρο το API σε machine-readable μορφή — **256 paths, 510 operations** |

> Όλες οι νέες δυνατότητες είναι **προαιρετικές** και συμβατές προς τα πίσω. Χωρίς τις νέες παραμέτρους,
> οι αποκρίσεις παραμένουν ίδιες με τις προηγούμενες εκδόσεις.

---

## Περιεχόμενα

| Φάκελος | Περιεχόμενο |
| --- | --- |
| [`postman/`](postman/) | Postman **collection** και **environment** (`{{base_url}}`, `{{authtoken}}`) |
| [`snippets/curl/`](snippets/curl/) | Έτοιμες εντολές `curl` |
| [`snippets/php/`](snippets/php/) | Παραδείγματα PHP (cURL) |
| [`snippets/python/`](snippets/python/) | Παραδείγματα Python (`requests`) |
| [`snippets/javascript/`](snippets/javascript/) | Παραδείγματα JavaScript / Node (`fetch`) |
| [`docs/`](docs/) | Authentication, pagination, filtering, webhooks, MCP, automation, custom tables και errors |

Κάθε γλώσσα περιλαμβάνει παραδείγματα για **customers, invoices, leads**, τις λειτουργίες v3
**webhooks, MCP, batch, automation, knowledge base, notes και optional modules**, καθώς και παράδειγμα
`list_features` για pagination, επιλογή πεδίων και sorting.

---

## Γρήγορη εκκίνηση

Κάθε αίτημα πιστοποιείται με το header **`Authtoken`**. Δημιουργήστε token στο eAD-CRM από
**API → API Management** και καλέστε το API στη διεύθυνση `https://yourdomain.com/api/...`:

```bash
curl -H "authtoken: YOUR_API_TOKEN" https://yourdomain.com/api/customers
```

Η απόκριση είναι η λίστα πελατών σε JSON. Δείτε το [`docs/authentication.md`](docs/authentication.md)
και τα παραδείγματα στον φάκελο [`snippets/`](snippets/).

### Χρήση του Postman collection

1. Στο Postman επιλέξτε **Import** και εισαγάγετε το [`postman/eadcrm-rest-api.postman_collection.json`](postman/eadcrm-rest-api.postman_collection.json).
2. Εισαγάγετε το environment [`postman/eadcrm-rest-api.postman_environment.json`](postman/eadcrm-rest-api.postman_environment.json).
3. Ορίστε το `base_url` σε `https://yourdomain.com/api` και το `authtoken` στο token σας.
4. Επιλέξτε ένα request και πατήστε **Send**.

### Σύνδεση AI agent μέσω MCP

Συνδέστε οποιονδήποτε MCP client (Claude Desktop, Cursor, ChatGPT, n8n AI Agent) στο
`POST https://yourdomain.com/api/mcp` και στείλτε το header `authtoken`. Δείτε το
[`docs/mcp.md`](docs/mcp.md) και το [`snippets/curl/mcp.sh`](snippets/curl/mcp.sh).

---

## Κατάλογος endpoints

Τα CRUD endpoints ακολουθούν τη σύμβαση REST: `GET` για λίστα, `GET /:id` για μία εγγραφή,
`POST` για δημιουργία, `PUT /:id` για ενημέρωση και `DELETE /:id` για διαγραφή.

### Βασικοί πόροι CRM

| Πόρος | Base path | Συνήθεις λειτουργίες |
| --- | --- | --- |
| Πελάτες | `/api/customers` | list, get, create, update, delete |
| Επαφές | `/api/contacts` | list, get, create, update, delete |
| Leads | `/api/leads` | list, get, create, update, delete |
| Τιμολόγια | `/api/invoices` | list, get, create, update, delete |
| Προσφορές | `/api/estimates` | list, get, create, update, delete |
| Πιστωτικά | `/api/credit_notes` | list, get, create, update |
| Πληρωμές | `/api/payments` | list, get, create |
| Προτάσεις | `/api/proposals` | list, get, create, update, delete |
| Συμβάσεις | `/api/contracts` | list, get, create, update, delete |
| Έργα | `/api/projects` | list, get, create, update, delete |
| Εργασίες | `/api/tasks` | list, get, create, update, delete |
| Ορόσημα | `/api/milestones` | list, get, create, update, delete |
| Timesheets | `/api/timesheets` | list, get, create, update, delete |
| Συνδρομές | `/api/subscriptions` | list, get, create, update |
| Είδη | `/api/items` | list, get, create, update, delete |
| Έξοδα | `/api/expenses` | list, get, create, update, delete |
| Προσωπικό | `/api/staffs` | list, get, create, update, delete |
| Ημερολόγιο | `/api/calendar` | list, get, create, update, delete |
| Custom Fields | `/api/custom_fields` | λίστα ανά related type |
| Common lookups | `/api/common` | χώρες, φόροι, νομίσματα, statuses κ.ά. |

### Πλατφόρμα v3 και πρόσθετοι πόροι

| Πόρος | Base path | Συνήθεις λειτουργίες |
| --- | --- | --- |
| **MCP server** | `/api/mcp` | `POST` JSON-RPC 2.0: `initialize`, `tools/list`, `tools/call` |
| **Batch** | `/api/batch` | `POST` έως 50 λειτουργίες |
| **Webhooks** | `/api/webhooks` | list, get, create, update, delete, toggle, events και logs |
| **Automation** | `/api/zapier` | resources, polling και test endpoints |
| **Knowledge Base** | `/api/knowledge_base` | CRUD άρθρων και groups |
| **Notes** | `/api/notes` | list, get, create, update, delete |
| **Warehouse** | `/api/warehouse` | αποθέματα, παραλαβές, παραδόσεις, μεταφορές και προσαρμογές |
| **Payments On Account** | `/api/paymentsonaccount` | αποδείξεις, κατανομές, statements, PDF και email |
| **Delivery Notes** | `/api/delivery_notes` | CRUD, statuses, PDF, email και μετατροπές εγγράφων |
| **Sales Commission** | `/api/commission` | policies, assignments, receipts, chart και recalculation |
| **MyShopify** | `/api/myshopify` | εισαγόμενα δεδομένα, mappings, logs και συγχρονισμός |
| **Purchase Management** | `/api/purchase` | προμηθευτές, έγγραφα προμηθειών, πληρωμές και statements |
| **Guest Invoices** | `/api/guest_invoices` | δημιουργία guest invoice και checkout |

Τα ακριβή πεδία κάθε request τεκμηριώνονται στον επίσημο
**[οδηγό API](https://apidoc.eadcrm.eu/)**. Για optional-module payloads δείτε το
[`docs/modules.md`](docs/modules.md).

---

## Pagination και filtering στη v3

Κάθε list endpoint δέχεται προαιρετικές παραμέτρους. Με αυτές επιστρέφει envelope `{ data, meta }`.
Χωρίς αυτές επιστρέφει το legacy array.

```bash
curl -H "authtoken: YOUR_API_TOKEN" \
  "https://yourdomain.com/api/customers?page=2&per_page=20&fields=id,company&sort=-datecreated&created_after=2026-01-01"
```

| Παράμετρος | Παράδειγμα | Αποτέλεσμα |
| --- | --- | --- |
| `page`, `per_page` | `?page=2&per_page=20` | Pagination με `{ data, meta }` |
| `fields` | `?fields=id,company` | Επιστρέφει μόνο τα επιλεγμένα πεδία |
| `sort` | `?sort=-datecreated,company` | Ταξινόμηση (`-` = φθίνουσα) |
| `created_after`, `created_before` | `?created_after=2026-01-01` | Φίλτρο ημερομηνιών |

Το `per_page` δέχεται τιμές 1–100 με προεπιλογή 25. Δείτε το
[`docs/pagination-filtering.md`](docs/pagination-filtering.md).

---

## Αναβάθμιση από την έκδοση 2.x

**Δεν υπάρχουν breaking changes.** Οι νέες δυνατότητες των list endpoints είναι opt-in:

- Χωρίς `page` ή `per_page` επιστρέφεται το ίδιο plain array της έκδοσης 2.x.
- Κανένα endpoint δεν μετονομάστηκε.
- Άγνωστα πεδία σε `PUT` αγνοούνται αντί να προκαλούν error.
- Τα `POST` requests δέχονται προαιρετικό header `Idempotency-Key` για ασφαλή retries.
- Τα νέα permission rows πρέπει να ενεργοποιηθούν στα υπάρχοντα API tokens.

---

## Δημοφιλείς διασυνδέσεις και χρήσεις

- **AI assistants μέσω MCP** — Claude, ChatGPT ή Cursor μπορούν να διαβάζουν και να ενημερώνουν το CRM.
- **Zapier / Make / n8n** — no-code automation μέσω polling triggers.
- **Webhooks** — αποστολή συμβάντων eAD-CRM σε Slack, Discord ή custom backend με υπογραφή HMAC.
- **Google Sheets / Power Automate** — συγχρονισμός πελατών, τιμολογίων και πληρωμών.
- **Custom apps και portals** — δημιουργία mobile εφαρμογής ή customer portal.
- **Λογιστική και e-commerce** — συγχρονισμός τιμολογίων και ειδών με τρίτα συστήματα.

---

## Authentication

| Μέθοδος | Χρήση |
| --- | --- |
| Header (προτεινόμενο) | `Authtoken: YOUR_API_TOKEN` |
| Query parameter | `?authtoken=YOUR_API_TOKEN` |

Τα tokens δημιουργούνται και αποκτούν permissions από το **API → API Management**. Περισσότερα στο
[`docs/authentication.md`](docs/authentication.md).

---

## Συχνές ερωτήσεις

**Διαθέτει το eAD-CRM REST API;**  
Ναι. Το [REST API για eAD-CRM](https://www.eadvertise.eu/) προσθέτει πλήρες HTTP/JSON API για τους
βασικούς πόρους του CRM, MCP server, webhooks, batch και automation endpoints.

**Μπορώ να συνδέσω το eAD-CRM με ChatGPT, Claude ή άλλους AI agents;**  
Ναι. Η v3 διαθέτει MCP server στο `POST /api/mcp`, με εργαλεία φιλτραρισμένα βάσει permissions.

**Πώς γίνεται το authentication;**  
Στείλτε το API token στο HTTP header `Authtoken` ή ως query parameter `?authtoken=`.

**Ποιο είναι το base URL;**  
`https://yourdomain.com/api`, για παράδειγμα `https://yourdomain.com/api/customers`.

**Υπάρχει Postman collection;**  
Ναι. Εισαγάγετε το [`postman/eadcrm-rest-api.postman_collection.json`](postman/eadcrm-rest-api.postman_collection.json)
και το αντίστοιχο environment.

---

## Σχετικά / Υποστήριξη

<img src="assets/eadcrm-rest-api-icon.svg" width="64" alt="Εικονίδιο eAD-CRM REST API">

Αυτό το repository συνοδεύει το εμπορικό module:

> **[REST API για eAD-CRM — συνδέστε το eAD-CRM με εφαρμογές τρίτων](https://www.eadvertise.eu/)**
> από την [eAdvertise](https://www.eadvertise.eu/).

- 🛒 **Αγορά / περισσότερες πληροφορίες:** https://www.eadvertise.eu/
- 📖 **Documentation:** https://apidoc.eadcrm.eu/
- 💬 **Υποστήριξη:** https://www.eadvertise.eu/

Οι συνεισφορές με πρόσθετα παραδείγματα είναι ευπρόσδεκτες — δείτε το [`CONTRIBUTING.md`](CONTRIBUTING.md).

## Άδεια χρήσης

Ο κώδικας παραδειγμάτων διατίθεται με την [άδεια MIT](LICENSE). Το «eAD-CRM» αποτελεί εμπορικό σήμα
του αντίστοιχου κατόχου, ενώ το REST API module είναι εμπορικό προϊόν της eAdvertise.

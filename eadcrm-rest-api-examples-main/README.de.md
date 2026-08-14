<p>
  <a href="https://www.eadvertise.eu/">
    <img src="assets/eadcrm-rest-api-banner.svg" alt="REST API for eAD-CRM — connect eAD-CRM with AI agents, Zapier, WooCommerce, n8n and third-party apps">
  </a>
</p>

# eAD-CRM REST API — Beispiele, Postman-Collection & Code-Snippets

[English](README.md) · [Ελληνικά](README.el.md) · [עברית](README.he.md) · [简体中文](README.zh-CN.md) · [Español](README.es.md) · [Português (BR)](README.pt-BR.md) · [Italiano](README.it.md) · [Français](README.fr.md) · 🌐 **Deutsch** · [Türkçe](README.tr.md) · [Tiếng Việt](README.vi.md) · [ไทย](README.th.md) · [العربية](README.ar.md)

> Sofort einsetzbare **Postman-Collection**, **Code-Snippets** (cURL, PHP, Python, JavaScript) und ein
> Ressourcen-**Katalog** für das [REST-API-Modul für eAD-CRM](https://www.eadvertise.eu/) —
> der schnellste Weg, um **eAD-CRM mit KI-Agenten und Drittanwendungen zu verbinden**.

[![Postman](https://img.shields.io/badge/Postman-Collection-orange?logo=postman&logoColor=white)](postman/eadcrm-rest-api.postman_collection.json)
[![OpenAPI 3.0](https://img.shields.io/badge/OpenAPI-3.0-6ba539?logo=openapiinitiative&logoColor=white)](https://apidoc.eadcrm.eu/)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![eAD-CRM](https://img.shields.io/badge/eAD-CRM-REST%20API-2c7be5)](https://www.eadvertise.eu/)

Mit der **eAD-CRM REST API** lesen und schreiben Sie Kunden, Leads, Rechnungen, Angebote, Projekte,
Aufgaben und mehr über eine saubere HTTP/JSON-Schnittstelle — ideal für **CRM-Integration**, Automatisierung und
eigene Anwendungen. **v3.0** ergänzt einen **MCP-Server für KI-Agenten**, produktionsreife **Webhooks**,
fertige **Zapier- / Make- / n8n**-Polling-Trigger, **Batch**-Operationen und intelligentere List-Endpunkte. Dieses Repository ist der praktische
Begleiter zum Modul
**[REST API for eAD-CRM](https://www.eadvertise.eu/)**
von **eAdvertise**: Copy-and-paste-Beispiele, eine importierbare Postman-Collection und ein vollständiger
Endpunkt-Katalog.

- 🧩 **Modul beziehen:** https://www.eadvertise.eu/
- 📖 **API-Leitfaden / Live-Doku:** https://apidoc.eadcrm.eu/
- 🧾 **OpenAPI-3.0-Spezifikation:** `GET https://yourdomain.com/api/openapi`

---

## 🚀 Was ist neu in v3.0

| Funktion | Endpunkt | Funktionsweise |
| --- | --- | --- |
| 🤖 **MCP-Server** | `POST /api/mcp` | Model Context Protocol (JSON-RPC 2.0) — stellt **148 berechtigungsgefilterte CRM-Tools** für Claude Desktop, ChatGPT, Cursor, n8n AI Agent und jeden MCP-Client bereit |
| 🪝 **Webhooks 2.0** | `/api/webhooks` | **124 Events**, REST-Verwaltung, asynchrone Zustellung mit Wiederholungen, SSRF-Schutz, **HMAC-signierte** Requests |
| 🔌 **Automatisierung (Polling)** | `/api/zapier/*` | Fertige Polling-Trigger für **Zapier, Make.com, n8n** und jedes Polling-basierte Tool |
| ⚡ **Batch** | `POST /api/batch` | Bis zu **50 Operationen** in einem Request (gleiche Tool-Namen wie MCP) |
| 📚 **Knowledge Base** | `/api/knowledge_base` | Artikel + Gruppen (CRUD) |
| 🗒️ **Notes** | `/api/notes` | Polymorphe Notizen über 12 Entitätstypen hinweg |
| 📄 **Intelligentere Listen** | jeder List-Endpunkt | Optional `?page=&per_page=`, `?fields=`, `?sort=`, `?created_after=&created_before=` |
| 🛡️ **Sichere Schreibvorgänge** | jeder `POST` | `Idempotency-Key`-Replay, ignorierte unbekannte Felder bei `PUT`, `X-RateLimit-*`-Header |

> Alles ist **opt-in** und abwärtskompatibel: Requests ohne die neuen Parameter liefern exakt
> dieselbe Antwort wie zuvor.

---

## Inhalt

| Ordner | Inhalt |
| --- | --- |
| [`postman/`](postman/) | Importierbare Postman-**Collection** + **Environment** (`{{base_url}}`, `{{authtoken}}`) — jetzt mit MCP, Webhooks, Batch, Automation, Knowledge Base & Notes |
| [`snippets/curl/`](snippets/curl/) | Copy-and-paste-`curl`-Befehle für die häufigsten Aufrufe |
| [`snippets/php/`](snippets/php/) | PHP-Beispiele (cURL) |
| [`snippets/python/`](snippets/python/) | Python-Beispiele (`requests`) |
| [`snippets/javascript/`](snippets/javascript/) | JavaScript- / Node-Beispiele (`fetch`) |
| [`docs/`](docs/) | Authentifizierung, Pagination & Filterung, Webhooks, MCP, Automatisierung, Fehler & Statuscodes |

Jede Snippet-Sprache enthält Beispiele für **customers, invoices, leads** sowie die v3-Funktionen
**webhooks, mcp, batch, automation, knowledge_base und notes** und eine **list_features**-Datei, die
Pagination, Feldauswahl und Sortierung zeigt.

---

## Schnellstart

Jeder Request an die eAD-CRM REST API wird über den **`Authtoken`**-Header authentifiziert. Erstellen Sie ein Token
im eAD-CRM-Adminbereich unter **API → API Management** (nachdem Sie das
[REST-API-Modul](https://www.eadvertise.eu/) aktiviert haben),
und rufen Sie die API dann unter `https://yourdomain.com/api/...` auf:

```bash
curl -H "authtoken: YOUR_API_TOKEN" https://yourdomain.com/api/customers
```

Das gibt die Kundenliste als JSON zurück. Siehe [`docs/authentication.md`](docs/authentication.md) für
Header- vs. Query-Parameter-Authentifizierung und [`snippets/`](snippets/) für denselben Aufruf in PHP, Python und JavaScript.

### Die Postman-Collection verwenden

1. Öffnen Sie Postman → **Import** → ziehen Sie [`postman/eadcrm-rest-api.postman_collection.json`](postman/eadcrm-rest-api.postman_collection.json) hinein.
2. Importieren Sie das Environment [`postman/eadcrm-rest-api.postman_environment.json`](postman/eadcrm-rest-api.postman_environment.json).
3. Setzen Sie `base_url` auf `https://yourdomain.com/api` und `authtoken` auf Ihr Token.
4. Wählen Sie einen beliebigen Request und klicken Sie auf **Send**.

### Einen KI-Agenten verbinden (MCP)

Richten Sie einen beliebigen MCP-Client (Claude Desktop, Cursor, ChatGPT, n8n AI Agent) auf `POST https://yourdomain.com/api/mcp`
und senden Sie Ihren `authtoken`-Header. Der Server bietet berechtigungsgefilterte Tools für Ihr CRM an. Siehe
[`docs/mcp.md`](docs/mcp.md) und [`snippets/curl/mcp.sh`](snippets/curl/mcp.sh).

---

## Endpunkt-Katalog

Alle CRUD-Endpunkte folgen einer RESTful-Konvention: `GET` Liste, `GET /:id` einzeln, `POST` erstellen,
`PUT /:id` aktualisieren, `DELETE /:id` löschen — unter dem Basis-Pfad `https://yourdomain.com/api`.

### Zentrale CRM-Ressourcen

| Ressource | Basis-Pfad | Typische Operationen |
| --- | --- | --- |
| Customers | `/api/customers` | list, get, create, update, delete |
| Contacts | `/api/contacts` | list, get, create, update, delete |
| Leads | `/api/leads` | list, get, create, update, delete |
| Invoices | `/api/invoices` | list, get, create, update, delete |
| Estimates | `/api/estimates` | list, get, create, update, delete |
| Credit Notes | `/api/credit_notes` | list, get, create, update |
| Payments | `/api/payments` | list, get, create |
| Proposals | `/api/proposals` | list, get, create, update, delete |
| Contracts | `/api/contracts` | list, get, create, update, delete |
| Projects | `/api/projects` | list, get, create, update, delete |
| Tasks | `/api/tasks` | list, get, create, update, delete |
| Milestones | `/api/milestones` | list, get, create, update, delete |
| Timesheets | `/api/timesheets` | list, get, create, update, delete |
| Subscriptions | `/api/subscriptions` | list, get, create, update |
| Items | `/api/items` | list, get, create, update, delete |
| Expenses | `/api/expenses` | list, get, create, update, delete |
| Staff | `/api/staffs` | list, get, create, update, delete |
| Calendar | `/api/calendar` | list, get, create, update, delete |
| Custom Fields | `/api/custom_fields` | Liste pro zugehörigem Typ |
| Common (Lookups) | `/api/common` | countries, taxes, currencies, statuses … |

### v3-Plattform- & Zusatzressourcen

| Ressource | Basis-Pfad | Typische Operationen |
| --- | --- | --- |
| **MCP-Server** | `/api/mcp` | `POST` JSON-RPC 2.0: `initialize`, `tools/list`, `tools/call` |
| **Batch** | `/api/batch` | `POST` bis zu 50 Operationen in einem Request |
| **Webhooks** | `/api/webhooks` | list, get, create, update, delete, `POST /:id/toggle`, `GET /events`, `GET /:id/logs` |
| **Automatisierung (Polling)** | `/api/zapier` | `GET /resources`, `GET /poll/:resource`, `GET /test/:resource` |
| **Knowledge Base** | `/api/knowledge_base` | list, get, create, update, delete; `/groups` |
| **Notes** | `/api/notes` | Liste nach `:rel_type/:rel_id`, get, create, update, delete |

> Die genauen Request-Felder je Ressource sind im offiziellen
> **[API-Leitfaden](https://apidoc.eadcrm.eu/)** dokumentiert. Die Snippets hier decken die häufigsten Abläufe ab.

---

## Intelligentere List-Endpunkte (v3)

Jeder List-Endpunkt akzeptiert optionale Query-Parameter. Fügen Sie sie hinzu und Sie erhalten einen `{ data, meta }`-Umschlag;
lassen Sie sie weg und Sie erhalten exakt das bisherige Array.

```bash
# Page 2, 20 per page, only id + company, newest first, created this year
curl -H "authtoken: YOUR_API_TOKEN" \
  "https://yourdomain.com/api/customers?page=2&per_page=20&fields=id,company&sort=-datecreated&created_after=2026-01-01"
```

| Parameter | Beispiel | Wirkung |
| --- | --- | --- |
| `page`, `per_page` | `?page=2&per_page=20` | Pagination → `{ data, meta }` |
| `fields` | `?fields=id,company` | Nur diese Spalten zurückgeben |
| `sort` | `?sort=-datecreated,company` | Sortierung (`-` = absteigend) |
| `created_after`, `created_before` | `?created_after=2026-01-01` | Datumsbereichsfilter |

Siehe [`docs/pagination-filtering.md`](docs/pagination-filtering.md) und
[`snippets/curl/list_features.sh`](snippets/curl/list_features.sh).

---

## Beliebte Integrationen & Anwendungsfälle

Die eAD-CRM REST API wird häufig genutzt, um **eAD-CRM mit KI-Agenten und Drittanwendungen zu verbinden**:

- **KI-Assistenten (MCP)** — lassen Sie Claude, ChatGPT oder Cursor Ihr CRM über `/api/mcp` lesen und aktualisieren.
- **Zapier / Make / n8n** — No-Code-Automatisierung über fertige Polling-Trigger (`/api/zapier/*`).
- **Webhooks** — leiten Sie eAD-CRM-Events (neue Rechnung, neuer Lead, 124 Events) an Slack, Discord oder Ihr eigenes Backend weiter, signiert mit HMAC.
- **Google Sheets / Power Automate** — synchronisieren Sie Kunden, Rechnungen oder Zahlungen mit Tabellen und Dashboards.
- **Eigene Apps & Portale** — entwickeln Sie eine mobile App oder ein Kundenportal auf Basis Ihrer eAD-CRM-Daten.
- **Buchhaltung & E-Commerce** — synchronisieren Sie Rechnungen und Artikel mit externen Abrechnungs- oder Shop-Plattformen.

All dies wird vom Modul
[REST API for eAD-CRM](https://www.eadvertise.eu/) ermöglicht.

---

## Authentifizierung (Zusammenfassung)

| Methode | Vorgehen |
| --- | --- |
| Header (empfohlen) | `Authtoken: YOUR_API_TOKEN` |
| Query-Parameter | `?authtoken=YOUR_API_TOKEN` (praktisch für schnelle Tests / Webhooks) |

Tokens werden unter **API → API Management** erstellt und mit ressourcenbezogenen Berechtigungen versehen. Alle Details in
[`docs/authentication.md`](docs/authentication.md).

---

## FAQ

**Hat eAD-CRM eine REST API?**
Ja. Das Modul [REST API for eAD-CRM](https://www.eadvertise.eu/)
ergänzt eine vollständige RESTful-HTTP/JSON-API für Kunden, Leads, Rechnungen, Angebote, Projekte, Aufgaben und mehr,
zuzüglich eines v3-**MCP-Servers** sowie **Webhooks**-, **Batch**- und **Automatisierungs**-Endpunkte.

**Kann ich eAD-CRM mit KI-Agenten / ChatGPT / Claude nutzen?**
Ja — v3 bringt einen **MCP-Server** unter `POST /api/mcp` mit, der berechtigungsgefilterte CRM-Tools für jeden
Model-Context-Protocol-Client bereitstellt. Siehe [`docs/mcp.md`](docs/mcp.md).

**Wie authentifiziere ich mich bei der eAD-CRM API?**
Senden Sie Ihr Token im HTTP-Header `Authtoken` (oder als Query-Parameter `?authtoken=`). Siehe
[`docs/authentication.md`](docs/authentication.md).

**Wie lautet die Basis-URL der eAD-CRM API?**
`https://yourdomain.com/api` — zum Beispiel `https://yourdomain.com/api/customers`.

**Kann ich eAD-CRM mit Zapier, Make oder n8n verbinden?**
Ja — v3 bietet fertige Polling-Trigger unter `/api/zapier/*` sowie Webhooks. Siehe
[Beliebte Integrationen](#popular-integrations--use-cases) und [`docs/automation.md`](docs/automation.md).

**Gibt es eine Postman-Collection für eAD-CRM?**
Ja — importieren Sie [`postman/eadcrm-rest-api.postman_collection.json`](postman/eadcrm-rest-api.postman_collection.json)
und das mitgelieferte Environment, setzen Sie Ihre `base_url` und Ihr `authtoken` und beginnen Sie mit dem Senden von Requests.

**Wie erstelle ich eine Rechnung über die eAD-CRM API?**
`POST https://yourdomain.com/api/invoices` mit den Rechnungsfeldern und einem `items[]`-Array — v3 berechnet
`subtotal`/`total` automatisch. Siehe [`snippets/curl/invoices.sh`](snippets/curl/invoices.sh).

---

## Über / Support

<img src="assets/eadcrm-rest-api-icon.svg" width="64" alt="eAD-CRM REST API icon">

Dieses Repository ist ein **Beispiel-Begleiter** zum kommerziellen Modul:

> **[REST API for eAD-CRM — connect your eAD-CRM with third-party applications](https://www.eadvertise.eu/)**
> von [eAdvertise](https://www.eadvertise.eu/).

- 🛒 **Kaufen / mehr erfahren:** https://www.eadvertise.eu/
- 📖 **Dokumentation:** https://apidoc.eadcrm.eu/
- 💬 **Support:** https://www.eadvertise.eu/

Beiträge mit zusätzlichen Beispielen sind willkommen — siehe [`CONTRIBUTING.md`](CONTRIBUTING.md).

## Lizenz

Der Beispielcode in diesem Repository steht unter der [MIT-Lizenz](LICENSE). „eAD-CRM" ist eine Marke
des jeweiligen Inhabers; das REST-API-Modul ist ein kommerzielles Produkt von eAdvertise.

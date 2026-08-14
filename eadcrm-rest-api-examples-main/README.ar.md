<p>
  <a href="https://www.eadvertise.eu/">
    <img src="assets/eadcrm-rest-api-banner.svg" alt="REST API for eAD-CRM — connect eAD-CRM with AI agents, Zapier, WooCommerce, n8n and third-party apps">
  </a>
</p>

# eAD-CRM REST API — أمثلة ومجموعة Postman ومقتطفات برمجية

[English](README.md) · [Ελληνικά](README.el.md) · [עברית](README.he.md) · [简体中文](README.zh-CN.md) · [Español](README.es.md) · [Português (BR)](README.pt-BR.md) · [Italiano](README.it.md) · [Français](README.fr.md) · [Deutsch](README.de.md) · [Türkçe](README.tr.md) · [Tiếng Việt](README.vi.md) · [ไทย](README.th.md) · 🌐 **العربية**

> **مجموعة Postman** جاهزة للاستخدام، و**مقتطفات برمجية** (cURL وPHP وPython وJavaScript)، و**دليل موارد**
> لأجل [وحدة REST API لنظام eAD-CRM](https://www.eadvertise.eu/) —
> أسرع طريقة **لربط eAD-CRM بوكلاء الذكاء الاصطناعي والتطبيقات الخارجية**.

[![Postman](https://img.shields.io/badge/Postman-Collection-orange?logo=postman&logoColor=white)](postman/eadcrm-rest-api.postman_collection.json)
[![OpenAPI 3.0](https://img.shields.io/badge/OpenAPI-3.0-6ba539?logo=openapiinitiative&logoColor=white)](https://apidoc.eadcrm.eu/)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![eAD-CRM](https://img.shields.io/badge/eAD-CRM-REST%20API-2c7be5)](https://www.eadvertise.eu/)

يتيح لك **eAD-CRM REST API** قراءة وكتابة العملاء والعملاء المحتملين والفواتير وعروض الأسعار والمشاريع
والمهام وغيرها عبر واجهة HTTP/JSON نظيفة — وهو مثالي **لتكامل الـ CRM** والأتمتة والتطبيقات المخصصة.
تضيف **الإصدار v3.0** خادم **MCP لوكلاء الذكاء الاصطناعي**، و**webhooks** بمستوى إنتاجي، وعمليات **استطلاع (polling)**
جاهزة لأجل **Zapier / Make / n8n**، وعمليات **الدفعات (batch)**، ونقاط نهاية قوائم أكثر ذكاءً. هذا المستودع هو الرفيق العملي
لوحدة
**[REST API for eAD-CRM](https://www.eadvertise.eu/)**
من **eAdvertise**: أمثلة جاهزة للنسخ واللصق، ومجموعة Postman قابلة للاستيراد، ودليل موارد كامل
لنقاط النهاية.

- 🧩 **احصل على الوحدة:** https://www.eadvertise.eu/
- 📖 **دليل الـ API / التوثيق المباشر:** https://apidoc.eadcrm.eu/
- 🧾 **مواصفات OpenAPI 3.0:** `GET https://yourdomain.com/api/openapi`

---

## 🚀 ما الجديد في الإصدار v3.0

| الميزة | نقطة النهاية | ما تقوم به |
| --- | --- | --- |
| 🤖 **خادم MCP** | `POST /api/mcp` | Model Context Protocol (JSON-RPC 2.0) — يوفر **148 أداة CRM مُرشَّحة حسب الصلاحيات** لأجل Claude Desktop وChatGPT وCursor وn8n AI Agent وأي عميل MCP |
| 🪝 **Webhooks 2.0** | `/api/webhooks` | **124 حدثاً**، إدارة عبر REST، تسليم غير متزامن مع إعادة المحاولة، حماية SSRF، طلبات **موقّعة بـ HMAC** |
| 🔌 **الأتمتة (الاستطلاع)** | `/api/zapier/*` | محفّزات استطلاع جاهزة لأجل **Zapier وMake.com وn8n** وأي أداة قائمة على الاستطلاع |
| ⚡ **الدفعات (Batch)** | `POST /api/batch` | حتى **50 عملية** في طلب واحد (بنفس أسماء أدوات MCP) |
| 📚 **قاعدة المعرفة** | `/api/knowledge_base` | مقالات + مجموعات بعمليات CRUD |
| 🗒️ **الملاحظات** | `/api/notes` | ملاحظات متعددة الأشكال عبر 12 نوعاً من الكيانات |
| 📄 **قوائم أكثر ذكاءً** | أي نقطة نهاية للقوائم | خيار اختياري: `?page=&per_page=`، `?fields=`، `?sort=`، `?created_after=&created_before=` |
| 🛡️ **كتابات آمنة** | أي `POST` | إعادة تشغيل `Idempotency-Key`، تجاهل الحقول غير المعروفة في `PUT`، ترويسات `X-RateLimit-*` |

> كل شيء **اختياري** ومتوافق مع الإصدارات السابقة: الطلبات التي لا تحتوي على المعاملات الجديدة تُرجع نفس
> الاستجابة تماماً كما في السابق.

---

## المحتويات

| المجلد | ما بداخله |
| --- | --- |
| [`postman/`](postman/) | **مجموعة** Postman قابلة للاستيراد + **بيئة** (`{{base_url}}`، `{{authtoken}}`) — الآن مع MCP وWebhooks وBatch والأتمتة وقاعدة المعرفة والملاحظات |
| [`snippets/curl/`](snippets/curl/) | أوامر `curl` جاهزة للنسخ واللصق لأكثر الاستدعاءات شيوعاً |
| [`snippets/php/`](snippets/php/) | أمثلة PHP (cURL) |
| [`snippets/python/`](snippets/python/) | أمثلة Python (`requests`) |
| [`snippets/javascript/`](snippets/javascript/) | أمثلة JavaScript / Node (`fetch`) |
| [`docs/`](docs/) | المصادقة، الترقيم والتصفية، webhooks، MCP، الأتمتة، الأخطاء ورموز الحالة |

كل لغة من لغات المقتطفات تحتوي على أمثلة لأجل **العملاء والفواتير والعملاء المحتملين** بالإضافة إلى ميزات الإصدار v3
**webhooks وmcp وbatch والأتمتة وknowledge_base وnotes**، وملف **list_features** يوضّح
الترقيم واختيار الحقول والترتيب.

---

## البدء السريع

كل طلب إلى eAD-CRM REST API يُصادَق عليه باستخدام ترويسة **`Authtoken`**. أنشئ رمزاً (token)
في لوحة إدارة eAD-CRM ضمن **API → API Management** (بعد تفعيل
[وحدة REST API](https://www.eadvertise.eu/))،
ثم استدعِ الـ API على `https://yourdomain.com/api/...`:

```bash
curl -H "authtoken: YOUR_API_TOKEN" https://yourdomain.com/api/customers
```

يُرجع ذلك قائمة العملاء بصيغة JSON. راجع [`docs/authentication.md`](docs/authentication.md) لمعرفة
المصادقة عبر الترويسة مقابل معامل الاستعلام، و[`snippets/`](snippets/) للاطلاع على نفس الاستدعاء بلغات PHP وPython وJavaScript.

### استخدام مجموعة Postman

1. افتح Postman ← **Import** ← أفلِت الملف [`postman/eadcrm-rest-api.postman_collection.json`](postman/eadcrm-rest-api.postman_collection.json).
2. استورد البيئة [`postman/eadcrm-rest-api.postman_environment.json`](postman/eadcrm-rest-api.postman_environment.json).
3. اضبط `base_url` على `https://yourdomain.com/api` و`authtoken` على الرمز الخاص بك.
4. اختر أي طلب واضغط **Send**.

### ربط وكيل ذكاء اصطناعي (MCP)

وجّه أي عميل MCP (Claude Desktop أو Cursor أو ChatGPT أو n8n AI Agent) إلى `POST https://yourdomain.com/api/mcp`
وأرسل ترويسة `authtoken` الخاصة بك. يعرض الخادم أدوات مُرشَّحة حسب الصلاحيات لأجل الـ CRM الخاص بك. راجع
[`docs/mcp.md`](docs/mcp.md) و[`snippets/curl/mcp.sh`](snippets/curl/mcp.sh).

---

## دليل نقاط النهاية

جميع نقاط نهاية CRUD تتّبع اصطلاحاً RESTful: `GET` للقائمة، و`GET /:id` لعنصر مفرد، و`POST` للإنشاء،
و`PUT /:id` للتحديث، و`DELETE /:id` للحذف — ضمن المسار الأساسي `https://yourdomain.com/api`.

### موارد الـ CRM الأساسية

| المورد | المسار الأساسي | العمليات المعتادة |
| --- | --- | --- |
| العملاء | `/api/customers` | list، get، create، update، delete |
| جهات الاتصال | `/api/contacts` | list، get، create، update، delete |
| العملاء المحتملون | `/api/leads` | list، get، create، update، delete |
| الفواتير | `/api/invoices` | list، get، create، update، delete |
| عروض الأسعار | `/api/estimates` | list، get، create، update، delete |
| الإشعارات الدائنة | `/api/credit_notes` | list، get، create، update |
| المدفوعات | `/api/payments` | list، get، create |
| المقترحات | `/api/proposals` | list، get، create، update، delete |
| العقود | `/api/contracts` | list، get، create، update، delete |
| المشاريع | `/api/projects` | list، get، create، update، delete |
| المهام | `/api/tasks` | list، get، create، update، delete |
| المعالم (Milestones) | `/api/milestones` | list، get، create، update، delete |
| سجلات الوقت (Timesheets) | `/api/timesheets` | list، get، create، update، delete |
| الاشتراكات | `/api/subscriptions` | list، get، create، update |
| العناصر (Items) | `/api/items` | list، get، create، update، delete |
| المصروفات | `/api/expenses` | list، get، create، update، delete |
| الموظفون | `/api/staffs` | list، get، create، update، delete |
| التقويم | `/api/calendar` | list، get، create، update، delete |
| الحقول المخصّصة | `/api/custom_fields` | القائمة حسب النوع المرتبط |
| العام (عمليات البحث) | `/api/common` | البلدان، الضرائب، العملات، الحالات … |

### منصة الإصدار v3 والموارد الإضافية

| المورد | المسار الأساسي | العمليات المعتادة |
| --- | --- | --- |
| **خادم MCP** | `/api/mcp` | `POST` بـ JSON-RPC 2.0: `initialize`، `tools/list`، `tools/call` |
| **الدفعات (Batch)** | `/api/batch` | `POST` حتى 50 عملية في طلب واحد |
| **Webhooks** | `/api/webhooks` | list، get، create، update، delete، `POST /:id/toggle`، `GET /events`، `GET /:id/logs` |
| **الأتمتة (الاستطلاع)** | `/api/zapier` | `GET /resources`، `GET /poll/:resource`، `GET /test/:resource` |
| **قاعدة المعرفة** | `/api/knowledge_base` | list، get، create، update، delete؛ `/groups` |
| **الملاحظات** | `/api/notes` | القائمة حسب `:rel_type/:rel_id`، get، create، update، delete |

> حقول الطلب الدقيقة لكل مورد موثّقة في **[دليل الـ API](https://apidoc.eadcrm.eu/)**
> الرسمي. تغطّي المقتطفات هنا أكثر التدفقات شيوعاً.

---

## نقاط نهاية القوائم الأكثر ذكاءً (v3)

تقبل كل نقطة نهاية للقوائم معاملات استعلام اختيارية. أضِفها فتحصل على غلاف `{ data, meta }`؛
واحذفها فتحصل على المصفوفة القديمة تماماً.

```bash
# Page 2, 20 per page, only id + company, newest first, created this year
curl -H "authtoken: YOUR_API_TOKEN" \
  "https://yourdomain.com/api/customers?page=2&per_page=20&fields=id,company&sort=-datecreated&created_after=2026-01-01"
```

| المعامل | مثال | التأثير |
| --- | --- | --- |
| `page`، `per_page` | `?page=2&per_page=20` | الترقيم ← `{ data, meta }` |
| `fields` | `?fields=id,company` | إرجاع هذه الأعمدة فقط |
| `sort` | `?sort=-datecreated,company` | الترتيب (`-` = تنازلي) |
| `created_after`، `created_before` | `?created_after=2026-01-01` | تصفية حسب نطاق التاريخ |

راجع [`docs/pagination-filtering.md`](docs/pagination-filtering.md) و
[`snippets/curl/list_features.sh`](snippets/curl/list_features.sh).

---

## التكاملات الشائعة وحالات الاستخدام

يُستخدم eAD-CRM REST API عادةً **لربط eAD-CRM بوكلاء الذكاء الاصطناعي والتطبيقات الخارجية**:

- **مساعدو الذكاء الاصطناعي (MCP)** — دع Claude أو ChatGPT أو Cursor يقرأ ويحدّث الـ CRM الخاص بك عبر `/api/mcp`.
- **Zapier / Make / n8n** — أتمتة بدون كود عبر محفّزات استطلاع جاهزة (`/api/zapier/*`).
- **Webhooks** — ادفع أحداث eAD-CRM (فاتورة جديدة، عميل محتمل جديد، 124 حدثاً) إلى Slack أو Discord أو الخادم الخلفي الخاص بك، موقّعة بـ HMAC.
- **Google Sheets / Power Automate** — زامِن العملاء أو الفواتير أو المدفوعات مع جداول البيانات ولوحات المعلومات.
- **التطبيقات والبوابات المخصّصة** — ابنِ تطبيقاً للهاتف المحمول أو بوابة عملاء فوق بيانات eAD-CRM الخاصة بك.
- **المحاسبة والتجارة الإلكترونية** — زامِن الفواتير والعناصر مع منصات الفوترة أو المتاجر الخارجية.

كل هذه المزايا مدعومة بوحدة
[REST API for eAD-CRM](https://www.eadvertise.eu/).

---

## المصادقة (ملخّص)

| الطريقة | الكيفية |
| --- | --- |
| الترويسة (موصى بها) | `Authtoken: YOUR_API_TOKEN` |
| معامل الاستعلام | `?authtoken=YOUR_API_TOKEN` (مفيد للاختبارات السريعة / webhooks) |

يتم إنشاء الرموز (tokens) وتحديد نطاقها (صلاحيات لكل مورد) في **API → API Management**. التفاصيل الكاملة في
[`docs/authentication.md`](docs/authentication.md).

---

## الأسئلة الشائعة

**هل يمتلك eAD-CRM واجهة REST API؟**
نعم. تضيف وحدة [REST API for eAD-CRM](https://www.eadvertise.eu/)
واجهة HTTP/JSON كاملة بأسلوب RESTful للعملاء والعملاء المحتملين والفواتير وعروض الأسعار والمشاريع والمهام وغيرها،
بالإضافة إلى **خادم MCP** و**webhooks** و**batch** ونقاط نهاية **الأتمتة** في الإصدار v3.

**هل يمكنني استخدام eAD-CRM مع وكلاء الذكاء الاصطناعي / ChatGPT / Claude؟**
نعم — يوفّر الإصدار v3 **خادم MCP** على `POST /api/mcp` يعرض أدوات CRM مُرشَّحة حسب الصلاحيات لأي
عميل Model Context Protocol. راجع [`docs/mcp.md`](docs/mcp.md).

**كيف أصادِق مع eAD-CRM API؟**
أرسل الرمز (token) الخاص بك في ترويسة `Authtoken` عبر HTTP (أو كمعامل استعلام `?authtoken=`). راجع
[`docs/authentication.md`](docs/authentication.md).

**ما هو عنوان URL الأساسي لأجل eAD-CRM API؟**
`https://yourdomain.com/api` — على سبيل المثال `https://yourdomain.com/api/customers`.

**هل يمكنني ربط eAD-CRM بـ Zapier أو Make أو n8n؟**
نعم — يحتوي الإصدار v3 على محفّزات استطلاع جاهزة ضمن `/api/zapier/*`، بالإضافة إلى webhooks. راجع
[التكاملات الشائعة](#popular-integrations--use-cases) و[`docs/automation.md`](docs/automation.md).

**هل توجد مجموعة Postman لأجل eAD-CRM؟**
نعم — استورد [`postman/eadcrm-rest-api.postman_collection.json`](postman/eadcrm-rest-api.postman_collection.json)
والبيئة المُرفقة، واضبط `base_url` و`authtoken` الخاصين بك، وابدأ بإرسال الطلبات.

**كيف أنشئ فاتورة عبر eAD-CRM API؟**
`POST https://yourdomain.com/api/invoices` مع حقول الفاتورة ومصفوفة `items[]` — يحسب الإصدار v3 تلقائياً
`subtotal`/`total`. راجع [`snippets/curl/invoices.sh`](snippets/curl/invoices.sh).

---

## حول / الدعم

<img src="assets/eadcrm-rest-api-icon.svg" width="64" alt="eAD-CRM REST API icon">

هذا المستودع هو **رفيق أمثلة** للوحدة التجارية:

> **[REST API for eAD-CRM — connect your eAD-CRM with third-party applications](https://www.eadvertise.eu/)**
> من [eAdvertise](https://www.eadvertise.eu/).

- 🛒 **الشراء / معرفة المزيد:** https://www.eadvertise.eu/
- 📖 **التوثيق:** https://apidoc.eadcrm.eu/
- 💬 **الدعم:** https://www.eadvertise.eu/

المساهمات بأمثلة إضافية مُرحَّب بها — راجع [`CONTRIBUTING.md`](CONTRIBUTING.md).

## الترخيص

الشيفرة البرمجية النموذجية في هذا المستودع منشورة بموجب [ترخيص MIT](LICENSE). "eAD-CRM" علامة تجارية مملوكة
لمالكها المعني؛ ووحدة REST API منتج تجاري من eAdvertise.

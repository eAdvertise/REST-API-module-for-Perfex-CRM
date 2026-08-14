/** eAD-CRM API v3 optional-module examples (Node.js 18+). */
const BASE = "https://yourdomain.com/api";
const TOKEN = "YOUR_API_TOKEN";
async function call(method, path, body) {
  const response = await fetch(BASE + path, { method, headers: { authtoken: TOKEN, ...(body ? { "Content-Type": "application/json" } : {}) }, body: body ? JSON.stringify(body) : undefined });
  if (!response.ok) throw new Error(`HTTP ${response.status}: ${await response.text()}`);
  return response.json();
}
(async () => {
  console.log(await call("GET", "/warehouse/inventory?page=1&per_page=25"));
  console.log(await call("POST", "/paymentsonaccount/receipts", { client_id: 12, amount: 150, payment_mode: "1", invoice_ids: [35] }));
  console.log(await call("POST", "/delivery_notes/notes", { clientid: 12, currency: 1, date: "2026-08-14", newitems: [{ description: "Delivered item", qty: 2, rate: 25 }] }));
  console.log(await call("GET", "/commission/commissions?page=1&per_page=25"));
  console.log(await call("POST", "/myshopify/sync"));
  console.log(await call("POST", "/purchase/requests", { currency: 1, subtotal: 100, newitems: [{ item_code: 10, quantity: 2, unit_price: 50 }] }));
  console.log(await call("POST", "/guest_invoices/checkout", { email: "guest@example.com", amount: 100, paymentmode: 1 }));
})().catch(console.error);

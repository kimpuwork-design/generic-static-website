<div class="card">
  <h2>API Documentation</h2>
  <p>Base endpoint: <code>index.php?route=api</code></p>
  <p>Authenticate using your API key: include <code>key</code> in POST body.</p>

  <h3>Endpoints</h3>
  <ul>
    <li><strong>Balance</strong>: <code>POST index.php?route=api&amp;action=balance</code> → { balance, currency }</li>
    <li><strong>Services</strong>: <code>POST index.php?route=api&amp;action=services</code> → [ { id, name, category, rate, min, max, type } ]</li>
    <li><strong>Add Order</strong>: <code>POST index.php?route=api&amp;action=add</code> with params <code>service</code>, <code>link</code>, <code>quantity</code> → { order }</li>
    <li><strong>Status</strong>: <code>POST index.php?route=api&amp;action=status</code> with param <code>order</code> → { status, charge }</li>
  </ul>

  <h3>Example</h3>
  <pre>
curl -X POST "https://yourdomain.com/index.php?route=api&amp;action=services" \
  -d "key=YOUR_API_KEY"
  </pre>
</div>
# Post-Mortem: Dashboard Freeze in Production (2026-02-04)

## Summary
On February 4th, 2026, dashboards in the production environment were reported as "frozen," showing persistent loading spinners instead of rendering widget data. Data was arriving in the browser (visible in DevTools Network tab), but the UI failed to update.

## Symptoms
- Widgets stuck in "Waiting for data..." state.
- Browser DevTools showed 200 OK responses with valid JSON payloads.
- Issue was non-reproducible in the local development environment.

## Root Causes

### 1. Strict Content-Type Validation
In `front.js`, the code was checking for an exact match of `application/json`.
- **Local environment**: Headers were clean `application/json`.
- **Production environment**: The server/proxy appended charset information (e.g., `application/json; charset=UTF-8`), causing the check to fail and the payload to be ignored.

### 2. Upstream Data Structure Variances
The upstream service (n8n) often returns data wrapped in an array `[ { "key": "value" } ]`.
- **Failure**: The initial `getNestedValue` implementation expected the object at the root. When receiving an array, it failed to find the keys, leaving widgets without data.

### 3. Aggressive Browser Caching
After initial fixes were deployed, the production environment continued to fail because browsers were serving a cached version of `front.js` from before the fixes.

### 4. Lack of Error Isolation
A failure in one widget's rendering logic (or a data mismatch) could potentially stall the entire rendering loop, providing no feedback to the user or developer.

## Resolution

### Frontend Resilience (`front.js`)
- **Robust Headers**: Changed `contentType.includes('application/json')` to be case-insensitive and allow for extra parameters.
- **Intelligent Pathing**: Added logic to automatically unwrap single-element arrays (n8n style) when resolving data keys.
- **Error Isolation**: Wrapped each widget's render call in a `try/catch` block. If one widget fails, others continue to load.
- **Debug Logging**: Added console groups and startup logs to provide immediate visibility into the engine's state and received data.

### Backend Stabilization
- **Explicit Headers**: Forced the `Content-Type: application/json` header in `FrontController` when returning sanitized payloads.

### Cache Busting
- **Deployment Strategy**: Added a timestamp-based versioning to the script tag in `page.blade.php` to ensure the latest code is always loaded:
  ```html
  <script src="{{ asset('js/front.js') }}?v={{ time() }}"></script>
  ```

## Lessons Learned
- Always use `.includes()` and `.toLowerCase()` when checking HTTP headers.
- Design data parsers to be "forgiving" regarding root arrays vs. objects, especially when proxying third-party services like n8n.
- Implement cache-busting for critical frontend assets from day one.
- Provide fallback UI states (like "--") instead of infinite spinners when data resolution fails.

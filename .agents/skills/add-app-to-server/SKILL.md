---
name: add-app-to-server
description: This skill should be used when the user asks to "add an app to my MCP server", "add UI to my MCP server", "add a view to my MCP tool", "enrich MCP tools with UI", "add interactive UI to existing server", "add MCP Apps to my server", or needs to add interactive UI capabilities to an existing MCP server that already has tools. Provides guidance for analyzing existing tools and adding MCP Apps UI resources.
---

# Add UI to MCP Server

Enrich an existing MCP server's tools with interactive UIs using the MCP Apps SDK (`@modelcontextprotocol/ext-apps`).

## How It Works

Existing tools get paired with HTML resources that render inline in the host's conversation. The tool continues to work for text-only clients — UI is an enhancement, not a replacement. Each tool that benefits from UI gets linked to a resource via `_meta.ui.resourceUri`, and the host renders that resource in a sandboxed iframe when the tool is called.

## Getting Reference Code

Clone the SDK repository for working examples and API documentation:

```bash
git clone --branch "v$(npm view @modelcontextprotocol/ext-apps version)" --depth 1 https://github.com/modelcontextprotocol/ext-apps.git /tmp/mcp-ext-apps
```

### API Reference (Source Files)

Read JSDoc documentation directly from `/tmp/mcp-ext-apps/src/`:

| File | Contents |
|------|----------|
| `src/app.ts` | `App` class, handlers (`ontoolinput`, `ontoolresult`, `onhostcontextchanged`, `onteardown`), lifecycle |
| `src/server/index.ts` | `registerAppTool`, `registerAppResource`, `getUiCapability`, tool visibility options |
| `src/spec.types.ts` | All type definitions: `McpUiHostContext`, CSS variable keys, display modes |
| `src/styles.ts` | `applyDocumentTheme`, `applyHostStyleVariables`, `applyHostFonts` |
| `src/react/useApp.tsx` | `useApp` hook for React apps |
| `src/react/useHostStyles.ts` | `useHostStyles`, `useHostStyleVariables`, `useHostFonts` hooks |

### Key Examples (Mixed Tool Patterns)

These examples demonstrate servers with both App-enhanced and plain tools — the exact pattern you're adding:

| Example | Pattern |
|---------|---------|
| `examples/map-server/` | `show-map` (App tool) + `geocode` (plain tool) |
| `examples/pdf-server/` | `display_pdf` (App tool) + `list_pdfs` (plain tool) + `read_pdf_bytes` (app-only tool) |
| `examples/system-monitor-server/` | `get-system-info` (App tool) + `poll-system-stats` (app-only polling tool) |

### Framework Templates

Learn and adapt from `/tmp/mcp-ext-apps/examples/basic-server-{framework}/`:

| Template | Key Files |
|----------|-----------|
| `basic-server-vanillajs/` | `server.ts`, `src/mcp-app.ts`, `mcp-app.html` |
| `basic-server-react/` | `server.ts`, `src/mcp-app.tsx` (uses `useApp` hook) |
| `basic-server-vue/` | `server.ts`, `src/App.vue` |
| `basic-server-svelte/` | `server.ts`, `src/App.svelte` |
| `basic-server-preact/` | `server.ts`, `src/mcp-app.tsx` |
| `basic-server-solid/` | `server.ts`, `src/mcp-app.tsx` |

## Step 1: Analyze Existing Tools

Before writing any code, analyze the server's existing tools and determine which ones benefit from UI.

1. Read the server source and list all registered tools
2. For each tool, assess whether it would benefit from UI (returns data that could be visualized, involves user interaction, etc.) vs. is fine as text-only (simple lookups, utility functions)
3. Identify tools that could become **app-only helpers** — endpoints that are only called from the UI and are never invoked by model-driven tool flows. Mark a tool as app-only helper (visibility: ["app"]) only when all three are true:
   1. it is never invoked by model-driven prompts or tool choreography,
   2. its sole consumers are UI code via `app.callServerTool()`, and
   3. it has lightweight auth or polling semantics suitable for client-side use.
4. Present the analysis to the user and confirm which tools to enhance

### Decision Framework

| Tool output type | UI benefit | Example |
|---|---|---|
| Structured data / lists / tables | High — interactive table, search, filtering | List of items, search results |
| Metrics / numbers over time | High — charts, gauges, dashboards | System stats, analytics |
| Media / rich content | High — viewer, player, renderer | Maps, PDFs, images, video |
| Simple text / confirmations | Low — text is fine | "File created", "Setting updated" |
| Data for other tools | Consider app-only | Polling endpoints, chunk loaders |

## Step 2: Add Dependencies

```bash
npm install @modelcontextprotocol/ext-apps
npm install -D vite vite-plugin-singlefile
```

Plus framework-specific dependencies if needed (e.g., `react`, `react-dom`, `@vitejs/plugin-react` for React).

For iterative local development, use `npm install <pkg>` to let npm resolve a compatible version rather than guessing version numbers from memory. For CI and production, pin versions or commit `package-lock.json` so builds remain reproducible, and use exact version specifiers when promoting releases.

## Step 3: Set Up the Build Pipeline

### Vite Configuration

Create `vite.config.ts` with `vite-plugin-singlefile` to bundle the UI into a single HTML file:

```typescript
import { defineConfig } from "vite";
import { viteSingleFile } from "vite-plugin-singlefile";

export default defineConfig({
  plugins: [viteSingleFile()],
  build: {
    outDir: "dist",
    rollupOptions: {
      input: "mcp-app.html", // one per UI, or one shared entry
    },
  },
});
```

### HTML Entry Point

Create `mcp-app.html` (or one per distinct UI if tools need different views):

```html
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MCP App</title>
  </head>
  <body>
    <div id="root"></div>
    <script type="module" src="./src/mcp-app.ts"></script>
  </body>
</html>
```

### Build Scripts

Add build scripts to `package.json`. Ensure `npm run build:ui` completes and produces `dist/mcp-app.html` before `npm run build:server` runs. Add a preflight check so builds fail fast if the UI artifact is missing.

```json
{
  "scripts": {
    "build:ui": "vite build",
    "build:server": "tsc",
    "build": "npm run build:ui && npm run build:server",
    "serve": "tsx server.ts"
  }
}
```

In your server startup or CI flow, verify `dist/mcp-app.html` exists and fail with an explicit error such as `Missing UI artifact: dist/mcp-app.html` if it does not.

## Step 4: Convert Tools to App Tools

Transform plain MCP tools into App tools with UI.

**Before** (plain MCP tool):
```typescript
server.tool("my-tool", { param: z.string() }, async (args) => {
  const data = await fetchData(args.param);
  return { content: [{ type: "text", text: JSON.stringify(data) }] };
});
```

**After** (App tool with UI):
```typescript
import { registerAppTool, registerAppResource, RESOURCE_MIME_TYPE } from "@modelcontextprotocol/ext-apps/server";

const resourceUri = "ui://my-tool/mcp-app.html";

registerAppTool(server, "my-tool", {
  description: "Shows data with an interactive UI",
  inputSchema: { param: z.string() },
  _meta: { ui: { resourceUri } },
}, async (args) => {
  const data = await fetchData(args.param);
  return {
    content: [{ type: "text", text: JSON.stringify(data) }],   // text fallback for non-UI hosts
    structuredContent: { data },                                 // structured data for the UI
  };
});
```

Key guidance:
- **Always keep the `content` array** with a text fallback for text-only clients
- Add `structuredContent` for data the UI needs to render
- Link the tool to its resource via `_meta.ui.resourceUri`
- Leave tools that don't benefit from UI unchanged — they stay as plain tools

## Step 5: Register Resources

Register the HTML resource so the host can fetch it:

```typescript
import fs from "node:fs/promises";
import path from "node:path";

const resourceUri = "ui://my-tool/mcp-app.html";

registerAppResource(server, {
  uri: resourceUri,
  name: "My Tool UI",
  mimeType: RESOURCE_MIME_TYPE,
}, async () => {
  let html;
  try {
    html = await fs.readFile(
      path.resolve(import.meta.dirname, "dist", "mcp-app.html"),
      "utf-8",
    );
  } catch (e) {
    console.error("Failed to load UI artifact", e);
    throw new Error("Missing UI build artifact: dist/mcp-app.html");
  }
  if (!html) {
    throw new Error("Missing UI build artifact: dist/mcp-app.html");
  }
  return { contents: [{ uri: resourceUri, mimeType: RESOURCE_MIME_TYPE, text: html }] };
});
```

If multiple tools share the same UI, they can reference the same `resourceUri` and the same resource registration.

## Step 6: Build the UI

### Handler Registration

Register all App instance event handlers before calling `app.connect()`:

```typescript
import { App, PostMessageTransport, applyDocumentTheme, applyHostStyleVariables, applyHostFonts } from "@modelcontextprotocol/ext-apps";

const app = new App({ name: "My App", version: "1.0.0" });

app.ontoolinput = (params) => {
  // Render the UI using params.arguments and/or params.structuredContent
};

app.ontoolresult = (result) => {
  // Update UI with final tool result
};

app.onhostcontextchanged = (ctx) => {
  if (ctx.theme) applyDocumentTheme(ctx.theme);
  if (ctx.styles?.variables) applyHostStyleVariables(ctx.styles.variables);
  if (ctx.styles?.css?.fonts) applyHostFonts(ctx.styles.css.fonts);
  if (ctx.safeAreaInsets) {
    const { top, right, bottom, left } = ctx.safeAreaInsets;
    document.body.style.padding = `${top}px ${right}px ${bottom}px ${left}px`;
  }
};

app.onteardown = async () => {
  return {};
};

try {
  await app.connect(new PostMessageTransport());
} catch (e) {
  console.error("App connect failed", e);
  throw new Error("App connect failed: unable to initialize the PostMessage transport");
}
```

Note: server-side registrations such as `registerAppTool` and `registerAppResource` can happen independently during server startup; they must be in place before the host first requests the tool or resource, but they do not need to be registered inside the App instance connect flow.

### Host Styling

Use host CSS variables for theme integration:

```css
.container {
  background: var(--color-background-secondary);
  color: var(--color-text-primary);
  font-family: var(--font-sans);
  border-radius: var(--border-radius-md);
}
```

Key variable groups: `--color-background-*`, `--color-text-*`, `--color-border-*`, `--font-sans`, `--font-mono`, `--font-text-*-size`, `--font-heading-*-size`, `--border-radius-*`. See `src/spec.types.ts` for the full list.

For React apps, use the `useApp` and `useHostStyles` hooks instead — see `basic-server-react/` for the pattern.

## Required vs Recommended

### Required (must do)
- Keep a text `content` fallback for non-UI hosts.
- Register all App instance event handlers before calling `app.connect()`.
- Ensure any `resourceUri` referenced by `_meta.ui.resourceUri` is registered before the host first requests it.
- Include `vite-plugin-singlefile` when bundling UI that will run inside a sandboxed iframe.
- Validate the UI artifact exists at startup and fail fast with a clear message if it does not.
- Validate `structuredContent` against a declared schema before returning it.

### Recommended (should do)
- Apply host CSS variables for theme integration.
- Handle `ctx.safeAreaInsets` in `onhostcontextchanged`.
- Declare CSP domains only when the UI actually requires them.
- Version structuredContent and provide backwards-compatible fallbacks for schema changes.

## Optional Enhancements

### App-Only Helper Tools

Tools the UI calls but the model doesn't need to invoke directly (polling, pagination, chunk loading):

```typescript
registerAppTool(server, "poll-data", {
  description: "Polls latest data for the UI",
  _meta: { ui: { resourceUri, visibility: ["app"] } },
}, async () => {
  const data = await getLatestData();
  return { content: [{ type: "text", text: JSON.stringify(data) }] };
});
```

The UI calls these via `app.callServerTool("poll-data", {})`.

### CSP Configuration

If the UI needs to load external resources (fonts, APIs, CDNs), declare the domains. Only include domains you control or have vetted, and avoid wildcards wherever possible.

Validate domain lists at build time and document why each domain is required. Reject registrations containing wildcards or third-party CDNs unless approved by a security review.

```typescript
registerAppResource(server, {
  uri: resourceUri,
  name: "My Tool UI",
  mimeType: RESOURCE_MIME_TYPE,
  _meta: {
    ui: {
      connectDomains: ["api.example.com"],      // fetch/XHR targets
      resourceDomains: ["cdn.example.com"],      // scripts, styles, images
      frameDomains: ["embed.example.com"],        // nested iframes
    },
  },
}, async () => { /* ... */ });
```

### Streaming Partial Input

For large tool inputs, show progress during LLM generation:

```typescript
app.ontoolinputpartial = (params) => {
  const args = params.arguments; // Healed partial JSON - always valid
  // Render preview with partial data
};

app.ontoolinput = (params) => {
  // Final complete input - switch to full render
};
```

### Graceful Degradation with `getUiCapability()`

If your server serves mixed client types, the safest default is to register both an App-enhanced tool and a plain tool fallback. That lets hosts choose the supported variant based on client capability, while still allowing text-only clients to function.

Option A — recommended for mixed clients: register both variants on startup.

```typescript
import { getUiCapability, registerAppTool, RESOURCE_MIME_TYPE } from "@modelcontextprotocol/ext-apps/server";

const resourceUri = "ui://my-tool/mcp-app.html";

registerAppTool(server, "my-tool-ui", {
  description: "Shows data with interactive UI",
  inputSchema: { param: z.string() },
  _meta: { ui: { resourceUri, visibility: ["client", "app"] } },
}, appToolHandler);

server.tool("my-tool", "Shows data", { param: z.string() }, plainToolHandler);
```

Option B — per-connection registration for platforms that support it: only register the App tool for UI-capable sessions and the plain tool for text-only sessions.

```typescript
server.server.oninitialized = () => {
  const clientCapabilities = server.server.getClientCapabilities();
  const uiCap = getUiCapability(clientCapabilities);

  if (uiCap?.mimeTypes?.includes(RESOURCE_MIME_TYPE)) {
    registerAppTool(server, "my-tool", {
      description: "Shows data with interactive UI",
      _meta: { ui: { resourceUri } },
    }, appToolHandler);
  } else {
    server.tool("my-tool", "Shows data", { param: z.string() }, plainToolHandler);
  }
};
```

Recommended default: register both variants on startup and let the host choose the supported tool, unless your server architecture explicitly supports per-connection registration.

### Fullscreen Mode

Allow the UI to expand to fullscreen:

```typescript
app.onhostcontextchanged = (ctx) => {
  if (ctx.availableDisplayModes?.includes("fullscreen")) {
    fullscreenBtn.style.display = "block";
  }
  if (ctx.displayMode) {
    container.classList.toggle("fullscreen", ctx.displayMode === "fullscreen");
  }
};

async function toggleFullscreen() {
  const newMode = currentMode === "fullscreen" ? "inline" : "fullscreen";
  const result = await app.requestDisplayMode({ mode: newMode });
  currentMode = result.mode;
}
```

## Common Mistakes to Avoid

1. **Forgetting text `content` fallback** — Always include `content` array with text for non-UI hosts
2. **Registering handlers after `connect()`** — Register App instance event handlers before calling `app.connect()`, and ensure tool/resource registration is available before the host first requests it.
3. **Missing `vite-plugin-singlefile`** — Without it, assets won't load in the sandboxed iframe
4. **Forgetting resource registration** — The tool references a `resourceUri` that must have a matching resource
5. **Hardcoding styles** — Use host CSS variables (`var(--color-*)`) for theme integration
6. **Not handling safe area insets** — Always apply `ctx.safeAreaInsets` in `onhostcontextchanged`

## Testing

### Using basic-host

Test the enhanced server with the basic-host example:

```bash
# Terminal 1: Build and run your server
npm run build && npm run serve

# Terminal 2: Run basic-host (from cloned repo)
cd /tmp/mcp-ext-apps/examples/basic-host
npm install
SERVERS='["http://localhost:3001/mcp"]' npm run start
# Open http://localhost:8080
```

Configure `SERVERS` with a JSON array of your server URLs (default: `http://localhost:3001/mcp`).

### Verify

1. Plain tools still work and return text output
2. App tools render their UI in the iframe
3. `ontoolinput` handler fires with tool arguments
4. `ontoolresult` handler fires with tool result
5. Host styling (theme, fonts, colors) applies correctly

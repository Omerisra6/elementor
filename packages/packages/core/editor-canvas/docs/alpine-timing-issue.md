# Alpine.js Timing Issue in Nested Templated Elements

## Problem Description

When rendering nested templated elements (like `e-tabs`) in the editor preview, Alpine.js throws errors because it tries to initialize elements before their data components are registered.

### Error Messages
```
Alpine Expression Error: Undefined variable: eTabsXXX
Alpine Expression Error: Undefined variable: tab
Alpine Expression Error: Undefined variable: tabContent
```

### Root Cause

The issue is a **timing mismatch** between:
1. **HTML injection** - When the twig template is rendered and injected into the DOM with `x-data="eTabsXXX"`
2. **Alpine data registration** - When the handler calls `Alpine.data('eTabsXXX', ...)`

#### Current Flow (Problematic)
```
1. _renderTemplate() → injects HTML with x-data="eTabsXXX"
2. Alpine's MutationObserver fires IMMEDIATELY when DOM changes
3. Alpine tries to find 'eTabsXXX' data component → NOT FOUND → ERROR
4. triggerMethod('render') → dispatches 'elementor/element/render' event
5. Handler receives event → calls Alpine.data('eTabsXXX', ...) → TOO LATE
```

Alpine's MutationObserver is synchronous - it fires immediately when DOM mutations occur, before any subsequent JavaScript can run.

### Additional Complexity

The `atomic-tabs-handler.js` reads from the DOM element:
- `element.dataset.id` - to get the tabs ID
- `element.getAttribute('data-e-settings')` - to get settings

If we dispatch the render event BEFORE injecting HTML, the element doesn't have these attributes yet.

---

## Attempted Solutions

### 1. Alpine.deferMutations() / flushAndStopDeferringMutations()

**Approach**: Pause Alpine's MutationObserver, inject HTML, trigger render event, then flush.

**Why it failed**: The `triggerMethod('render')` dispatches an event to the preview iframe. Even though `dispatchEvent` is synchronous, by the time we call `flushAndStopDeferringMutations()`, the handler hasn't finished registering the Alpine data because the event processing happens in the preview window context.

### 2. Dispatch render event before HTML injection

**Approach**: Call `triggerMethod('render')` before `$el.html(html)` so the handler registers Alpine.data first.

**Why it failed**: The handler needs to read `element.dataset.id` and `data-e-settings` from the DOM element, but these attributes don't exist until after HTML injection.

---

## Proposed Solutions

### Solution A: Two-Phase Event System

Create a new `elementor/element/pre-render` event that fires before HTML injection, passing the element ID and settings directly in the event detail (not from DOM).

**Changes required**:
1. Add new event dispatch in `create-nested-templated-element-type.ts` before HTML injection
2. Update `frontend-handlers/src/init.ts` to listen to `pre-render` event
3. Update `lifecycle-events.ts` to pass `elementId` and `settings` from event detail to handlers
4. Update handlers to use `elementId` parameter instead of `element.dataset.id`

**Pros**: Clean separation of concerns
**Cons**: Requires API changes to frontend-handlers

### Solution B: Pass elementId and settings in existing render event

Modify the existing render event to include `elementId` and `settings` in the event detail, so handlers don't need to read from DOM.

**Changes required**:
1. Update event dispatch to include settings in detail
2. Update `lifecycle-events.ts` to pass `elementId` to handler callback
3. Update `lifecycle-events.ts` to prefer settings from event detail over DOM attribute
4. Update handlers to use `elementId` parameter

**Pros**: Minimal API surface change
**Cons**: Still requires handler signature change

### Solution C: Use x-ignore attribute

Add `x-ignore` attribute to the twig template, then remove it and call `Alpine.initTree()` after the handler registers the data.

**Changes required**:
1. Add `x-ignore` to twig template
2. After render event, remove `x-ignore` and call `Alpine.initTree(element)`

**Pros**: No API changes needed
**Cons**: Requires manual Alpine initialization, may have edge cases

### Solution D: Pre-register Alpine data components

Register all Alpine data components upfront when the element type is registered, using a factory pattern that receives the element ID at runtime.

**Changes required**:
1. Change how Alpine.data is registered - use a factory that creates the component dynamically
2. Register a single `eTabs` component that reads its ID from the DOM at init time

**Pros**: No timing issues, no API changes
**Cons**: Requires rethinking how Alpine data components are structured

---

## Recommended Approach

**Solution B** is recommended as it:
1. Solves the timing issue completely
2. Has minimal API surface changes
3. Is backwards compatible (handlers can still read from DOM if needed)
4. Aligns with how the event already passes `id` and `type`

### Implementation Steps

1. Update `lifecycle-events.ts`:
   - Add `elementId` to handler callback parameters
   - Accept optional `settings` from event detail, fall back to DOM attribute

2. Update `init.ts`:
   - Pass `settings` from event detail to `onElementRender`

3. Update `create-element-type.ts` (for nested templated elements):
   - Include resolved settings in event detail

4. Update handlers (e.g., `atomic-tabs-handler.js`):
   - Use `elementId` parameter instead of `element.dataset.id`


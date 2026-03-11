# Epic Estimation Report — Q1 2026

> This report was prepared to support the T-shirt size estimation process.  
> Each epic includes open questions for the product team, identified risks, and decisions that can simplify or de-risk scoping.

---

## Category: Atomic Approach Comprehension Gap

---

### Epic: No Structure / Getting Started

**Description:** Help users get started with v4 — pre-defined classes, variables, components, and more.

#### Open Questions
1. Who is the target user — a first-time Elementor user, an existing user creating a new v4 site, or someone migrating from v3?
2. What does "getting started" look like in practice — a guided wizard, a starter kit applied on site creation, or something available on-demand?
3. Are the pre-defined classes and variables meant to be a starting point (editable/deletable) or a locked foundation?
4. Should this experience appear every time a new site is created, or only once per user?
5. Is there a design or content team responsible for the pre-defined starter assets, or is this owned by engineering?

#### Risks
- "Getting started" is a broad concept — without a clear scope, this could expand significantly beyond the current XS estimate.
- Pre-defined content that doesn't match a user's brand may create confusion or feel like clutter rather than help.
- If starter assets can be removed, there's a risk of breaking the onboarding flow for users who accidentally delete them.

#### Decisions That Would Help
- Defining the single entry point (new site creation vs. empty canvas) would lock the scope early.
- Deciding whether this is a one-time guided flow or a persistent library significantly changes the complexity.

---

### Epic: V4 Education

**Description:** Best practices, tips, in-app guidance, and addressing the variable vs. class distinction confusion.

#### Open Questions
1. What format should education take — contextual tooltips, a dedicated onboarding flow, a help panel, or links to external documentation?
2. Is this targeted at brand new users, users migrating from v3, or both?
3. Who owns the content — does the product team provide copy and structure, or is the dev team expected to define it?
4. Is the variable vs. class confusion a specific, isolated problem, or does it represent a broader conceptual gap that needs a design-level solution?
5. Is there existing user research or data (e.g., support tickets, session recordings) that defines which concepts are most confusing?

#### Risks
- Without defined content ownership, this could become a blocker waiting for copy/UX decisions.
- In-app education that is too visible becomes noise; too subtle and it gets ignored — the balance is hard to get right without user testing.

#### Decisions That Would Help
- Deciding upfront if this is a UX/copy task or an engineering task will clarify who drives it.
- Prioritizing the single most impactful concept to address (e.g., variable vs. class) allows a scoped first iteration rather than a full education system.

---

## Category: V4 Completeness Gap

---

### Epic: Accordion

**Description:** A v4-native Accordion widget.

#### Open Questions
1. Does this need to support migration from the existing v3 Accordion widget, or is it a net-new element?
2. What interaction behaviors are expected — animated expand/collapse, multiple open at once, first item open by default?
3. Are there accessibility requirements in scope (keyboard navigation, ARIA roles)?

#### Risks
- If v3 migration is in scope, the effort jumps significantly from the current XS estimate.

#### Decisions That Would Help
- Clarifying whether v3 migration is in or out of scope for this quarter is the single most important decision.

---

### Epic: Menu

**Description:** A v4-native Menu widget.

#### Open Questions
1. Must this integrate with the WordPress native menu system, or is it a standalone v4 element?
2. What is the expected mobile behavior — hamburger toggle, slide-out drawer, dropdown?
3. Is the Mega Menu epic a dependent follow-up to this, or are they fully independent?
4. Does this need to work inside Loop containers?

#### Risks
- WordPress menu integration adds significant complexity and dependency on WordPress core behavior.
- If Mega Menu is treated as an extension of this epic, scope can grow unexpectedly mid-sprint.

#### Decisions That Would Help
- Explicitly separating the basic Menu from Mega Menu functionality upfront prevents scope creep.
- Deciding whether WordPress menu integration is required at launch or a v2 addition would clarify the estimate range significantly.

---

### Epic: Layouts

**Description:** Missing v4 layout capabilities (specific layouts unclear from the title).

#### Open Questions
1. What specific layout types are missing in v4 that exist in v3 or are expected by users?
2. Is this about layout presets (e.g., header/footer/section templates) or layout controls (e.g., sticky, full-width, overlapping)?
3. How does this relate to the "Layouts" items in *Design System Portability* and *Dual Design System Gap* — are these three distinct features or overlapping?

#### Risks
- "Layouts" appears three times in the roadmap with different contexts and no clear differentiation. This ambiguity alone is a risk.

#### Decisions That Would Help
- Defining a clear, single-sentence scope for each of the three "Layouts" epics would allow proper estimation. As-is, they cannot be estimated independently.

---

### Epic: Nested Carousel

**Description:** Support for a carousel widget inside another carousel or nested context.

#### Open Questions
1. What is the primary use case driving this request — is there user research or customer demand behind it?
2. Must it support touch/swipe on mobile?
3. Does "nested" mean a carousel inside a carousel specifically, or a carousel inside any container (e.g., a tab, an accordion)?

#### Risks
- Nested interactive elements are inherently complex for accessibility and touch interaction — the S estimate may be optimistic without a clear scope on interactions.

#### Decisions That Would Help
- Limiting the first version to a specific nesting pattern (e.g., carousel inside a tab only) would contain the scope.

---

### Epic: Lists

**Description:** A v4-native List widget.

#### Open Questions
1. What list types are in scope — ordered, unordered, icon-based, definition lists, or all of the above?
2. Should individual list items be independently styleable, or is the list styled as a unit?
3. Should list items support dynamic data binding?

#### Decisions That Would Help
- Scoping to a single list type (e.g., unordered + icon support) for the first iteration allows a clear S estimate.

---

### Epic: Real v4 SVG

**Description:** Proper SVG support within v4.

#### Open Questions
1. What does "real v4 SVG" mean for users — the ability to use SVGs as icons, embed inline SVG, or style SVG paths with design tokens?
2. Should SVG colors be controllable via v4 variables?
3. Are there security requirements around SVG uploads from untrusted sources (this is a common attack vector)?

#### Risks
- Allowing inline SVG from user uploads introduces XSS security risks that require a deliberate mitigation strategy.

#### Decisions That Would Help
- Deciding whether this is about SVG *rendering* (display existing SVGs properly) or SVG *editing/styling* (controlling SVG internals) changes the scope entirely.

---

### Epic: CSS Grid

**Description:** A v4-native CSS Grid layout container.

#### Open Questions
1. Is this a full CSS Grid implementation (with template areas, named lines, etc.) or a simplified visual grid builder?
2. Should it support different grid configurations per breakpoint?
3. Is this intended as a replacement for the current container/flexbox approach, or an alternative layout option alongside it?
4. Are there existing design mockups that define the expected UX?

#### Risks
- Full CSS Grid is a large surface area — without scope boundaries, the M estimate could easily become an L or XL.
- Adding a second layout paradigm (grid alongside flexbox) increases the mental model complexity for users.

#### Decisions That Would Help
- Defining the minimum grid capabilities for v1 (e.g., columns + rows only, no template areas) would lock the estimate.

---

### Epic: Loop Grid *(In Progress)*

**Description:** A dynamic grid that renders content from a data query.

#### Open Questions
1. What is the current completion percentage, and what specific functionality remains?
2. Is the *New Query Mechanism* epic a blocker for completion, or can Loop Grid ship with the existing query approach?
3. What data sources must be supported at launch — WordPress posts only, or also custom post types, taxonomies, and third-party sources?

#### Risks
- The dependency on the Query Mechanism epic creates a sequencing risk — if that slips, Loop Grid cannot ship.

#### Decisions That Would Help
- Explicitly defining whether Loop Grid ships with a temporary query approach or waits for the new mechanism would unblock planning.

---

### Epic: Carousel Loop *(In Progress)*

**Description:** A carousel-style widget that renders dynamic content from a query.

#### Open Questions
1. Is this dependent on Loop Grid being complete, or is it built independently?
2. What is the current completion status and what remains?
3. Does it share the same query mechanism as Loop Grid?

#### Risks
- Shared dependencies with Loop Grid and the Query Mechanism epic create a risk of cascading delays.

---

### Epic: Mega Menu

**Description:** An extended menu widget supporting rich, panel-based navigation.

#### Open Questions
1. Is this an extension of the Menu epic, or a fully separate widget?
2. What content is allowed inside a Mega Menu panel — any v4 element, or a limited set?
3. What is the expected trigger behavior — hover, click, or configurable?
4. Is there a design available that defines the expected experience?

#### Risks
- If Mega Menu allows arbitrary v4 elements inside panels, the scope is very large and the L estimate could grow.
- This has a logical dependency on the Menu epic — sequencing matters.

#### Decisions That Would Help
- Deciding whether panel content is freeform or template-based significantly changes the complexity.

---

### Epic: Icons Library

**Description:** A v4 icon management system.

#### Open Questions
1. Is the goal to integrate with existing icon packs (Font Awesome, etc.), support custom SVG icon uploads, or build a proprietary library?
2. Should icon colors be controllable via v4 design system variables?
3. Does this replace or extend the current icon widget?
4. Is search and filtering a requirement at launch?
5. Should icons be available as standalone elements or only as properties of other widgets (e.g., button icon)?

#### Risks
- This is currently estimated as XL (80+ SP) — the widest possible estimate. The actual scope is unclear, which is itself a risk signal.
- Building a proprietary icon system vs. integrating an existing one is a very different effort.

#### Decisions That Would Help
- The most important decision is: build vs. integrate. Choosing to integrate an existing system (e.g., Font Awesome) scopes this dramatically.

---

### Epic: New Query Mechanism for Loops

**Description:** A new system for querying data to power Loop Grid, Carousel Loop, and related dynamic elements.

#### Open Questions
1. What data sources must be supported at launch — WordPress posts, custom post types, WooCommerce products, ACF fields, or others?
2. Is this replacing the existing query control entirely, or living alongside it?
3. What is the intended user — a designer who wants a simple "show latest 6 posts" interface, or a developer who wants full query control?
4. Are there specific third-party query integrations required at launch?

#### Risks
- This is a foundational dependency for Loop Grid, Carousel Loop, and potentially other future epics — a delay here has cascading effects.
- The scope of "loops and stuff" is intentionally vague — without a defined feature list, estimation is unreliable.

#### Decisions That Would Help
- Defining the minimum supported query types for v1 (e.g., WP_Query only) allows a reliable L estimate.
- Deciding whether developer-facing query filters/hooks are in scope for v1 or v2 changes the estimate significantly.

---

## Category: Dual Design System Gap

---

### Epic: Colors — Migration from v3 to v4

**Description:** Allow v3 color settings to be migrated to the v4 design system.

#### Open Questions
1. Is migration automatic, guided (step-by-step wizard), or manual?
2. What happens to v3 color values that do not map cleanly to v4 variables (e.g., inline hex values on individual elements)?
3. Can users roll back a migration if they are unhappy with the result?
4. Is partial migration supported — e.g., migrating colors but not fonts?
5. Does this affect all users who upgrade, or only those who explicitly choose to migrate?

#### Risks
- An automatic migration that modifies site appearance without user consent is a critical trust and data integrity risk.
- The current L estimate could grow if rollback functionality is required.

#### Decisions That Would Help
- Deciding on automatic vs. opt-in migration upfront is the most important product decision and directly determines the safety requirements.

---

### Epic: Fonts — Migration from v3 to v4

**Description:** Allow v3 font settings to be migrated to the v4 design system.

#### Open Questions
1. Same migration flow questions as Colors — is it automatic or opt-in?
2. Are custom uploaded fonts and Google Fonts both in scope?
3. Should this migration run independently of the Colors migration, or as part of a single "migrate my site" flow?

#### Risks
- Same rollback and data integrity risks as the Colors migration.

#### Decisions That Would Help
- Combining Colors and Fonts into a single migration flow may be a better UX and could simplify the overall effort vs. two separate systems.

---

### Epic: Typography (Site Settings > Theme Style)

**Description:** A v4 UI for defining global typography — H1–H6, body text.

#### Open Questions
1. Which typography properties are configurable per heading level — font family, size, weight, line height, letter spacing, color?
2. Should these map directly to v4 variables, or are they standalone settings that exist separately?
3. How does this interact with a WordPress theme that already defines heading styles — which takes precedence?
4. Is there a design available, or does UX/design need to be created first?

#### Risks
- The current estimate is marked "???" — this cannot be estimated without a clearer definition of scope.
- Conflicts between v4 typography settings and active WordPress theme styles could cause unexpected visual regressions for users.

#### Decisions That Would Help
- Clarifying the relationship between these settings and v4 design system variables would unlock the estimate.

---

### Epic: Layouts (Site Settings > Settings)

**Description:** Site-level layout settings.

#### Open Questions
1. What layout properties are configurable at the site level — container width, column gap, section padding, breakpoint definitions?
2. How does this differ from the "Layouts" epic in the V4 Completeness Gap category?
3. Should site-level layout settings override or inform individual page/element settings, or only serve as defaults?

#### Risks
- This is marked "???" with no estimate — the description is too vague to assess.
- Three separate "Layouts" epics in the roadmap with no differentiation is a planning risk.

#### Decisions That Would Help
- Providing a concrete list of the layout settings to be included would move this from "???" to an estimable item.

---

### Epic: Buttons / Images / Icons (Site Settings > Theme Style)

**Description:** Global default styles for buttons, images, and icons.

#### Open Questions
1. Which button properties are configurable globally — size, border radius, background color, hover state, font?
2. Do global defaults auto-apply to all existing elements on the site, or only to new elements created after the setting is defined?
3. Are these settings connected to v4 design system variables, or are they independent?

#### Risks
- If global defaults auto-apply retroactively, this could visually break existing sites — requires a clear policy decision before estimation.

#### Decisions That Would Help
- Deciding whether global defaults are opt-in per element or automatically applied to all elements is the most impactful scoping decision.

---

### Epic: Site / Page Background (Site Settings > Settings)

**Description:** Global and per-page background settings.

#### Open Questions
1. What background types are in scope — solid color, gradient, image, video?
2. Should site-level background be overridable at the page level?
3. Should backgrounds support responsive variations (different background per breakpoint)?

#### Decisions That Would Help
- Limiting the first version to color and image backgrounds (excluding video) would bring this from L to a more predictable estimate.

---

## Category: Design System Portability

---

### Epic: Import / Export Variables in Manager

**Description:** Allow users to import and export their v4 design system variables.

#### Open Questions
1. What file format should be used — JSON, CSS custom properties, or a design token standard (e.g., W3C Design Tokens format)?
2. Should the export include variable values, or only the token names and structure?
3. Is the primary use case site-to-site transfer, team sharing, or integration with external design tools (e.g., Figma Tokens)?
4. What happens when importing variables that conflict with existing ones — merge, overwrite, or prompt the user?

#### Decisions That Would Help
- Choosing a file format upfront avoids mid-sprint rework — the W3C tokens format is worth evaluating for long-term compatibility.

---

### Epic: Access v4 Design System from Topbar

**Description:** Quick access to the v4 design system from the editor topbar.

#### Open Questions
1. What actions should be available from the topbar — view only, edit, import/export?
2. Is this a dropdown panel, a slide-out sidebar, or a modal?
3. Should it be available when editing both v3 and v4 elements, or only in v4 mode?

#### Risks
- The estimate is "???" — the scope is wide open.

#### Decisions That Would Help
- Defining this as "read-only quick access" vs. "full design system editor in the topbar" changes the scope dramatically.

---

### Epic: Layouts (Design System Portability)

**Description:** Layout settings as part of the portable design system.

#### Open Questions
1. What layout properties are portability-relevant — spacing scales, breakpoint definitions, container widths?
2. How does this differ from the other two "Layouts" epics in the roadmap?
3. Should layout tokens be exportable as part of the standard import/export flow?

#### Risks
- Same three "Layouts" ambiguity risk as noted above — this cannot be estimated independently until all three are differentiated.

---

### Epic: Variables — Filter & Sort

**Description:** Filtering and sorting controls in the variables manager.

#### Open Questions
1. What filter dimensions are expected — by type (color, spacing, etc.), by group, by usage status (used/unused), by name?
2. Should filter preferences persist between sessions?
3. Should this filtering be available only in the manager, or also in the inline variable pickers throughout the editor?

#### Decisions That Would Help
- Scoping filtering to the manager only (not inline pickers) for the first iteration keeps this an M rather than expanding further.

---

### Epic: Variables — Bulk Actions

**Description:** Bulk operations on variables in the manager.

#### Open Questions
1. What bulk actions are in scope — delete, move to group, export selection, bulk rename?
2. Should bulk actions be undoable (undo/redo support)?

#### Decisions That Would Help
- Defining the specific list of actions upfront prevents scope creep — "bulk actions" without a list is not estimable.

---

### Epic: Variables — Groups

**Description:** Grouping/organizing variables in the manager.

#### Open Questions
1. Is grouping hierarchical (groups within groups) or flat (one level only)?
2. Can a variable belong to multiple groups?
3. Is this related to the "Group Classes" epic, or are classes and variables grouped independently?

#### Decisions That Would Help
- Flat grouping (one level) is significantly simpler than hierarchical — deciding this upfront locks the estimate.

---

### Epic: Variables — Responsiveness

**Description:** Support for responsive variable values (different value per breakpoint).

#### Open Questions
1. Which variable types support responsive values — spacing only, or also colors, typography, and others?
2. How should a responsive variable be surfaced in the UI when a user applies it — does it show the current breakpoint's value, or all values?
3. How do conflicts between a responsive variable and a responsive override on an individual element get resolved?

#### Risks
- This is marked "???" — responsive variable support is a significant architectural feature and the estimate gap suggests the scope is unclear.
- This could have ripple effects across the editor's control system — a risk the current estimate likely doesn't reflect.

#### Decisions That Would Help
- Scoping to spacing/sizing variables only for the first iteration would allow an initial estimate.

---

## Category: Performance and Trust

---

### Epic: Real v4 Flexbox

**Description:** A proper v4 flexbox implementation.

#### Open Questions
1. What specific capabilities are missing or broken in the current flexbox implementation?
2. Is this a replacement of existing behavior (which would require migration) or additive?
3. Are there known user-facing bugs or limitations that are documented?

#### Risks
- Replacing existing flexbox behavior risks breaking existing sites — this needs a very clear definition of what changes vs. what stays the same.
- Estimated as XL — the largest possible bucket — with a "???" team assignment. This needs more definition before it can be reliably planned.

#### Decisions That Would Help
- Defining a specific list of missing flexbox capabilities (vs. a broad "redo") would transform this from an unpredictable XL into something estimable.

---

### Epic: CSS Value Bugs

**Description:** Bug fixes for specific CSS properties: z-index, font weight, columns, gradient, box shadow, transform, transitions, filters, backdrop filters.

#### Open Questions
1. Is there a prioritized bug list for these properties, or is the expectation to address all of them?
2. Are these bugs v4-only, or do some also exist in v3?
3. What is the user-facing severity — are these visual glitches, broken layouts, or complete functionality failures?

#### Risks
- The comment in the sheet says "this could be many different things" — this is not a scoped epic, it's an open-ended bug category.
- Without a defined list of bugs, any estimate is a guess.

#### Decisions That Would Help
- Creating a prioritized bug list before estimation is the prerequisite here — estimation cannot be accurate without it.

---

### Epic: Variables Coverage

**Description:** Expand which CSS properties support variable binding.

#### Open Questions
1. Which specific CSS properties currently cannot be bound to variables?
2. Is the goal full coverage, or coverage of the highest-impact properties?
3. Is there user data or demand signal to prioritize specific properties?

#### Decisions That Would Help
- Producing a list of the top 5–10 missing properties would allow an estimate — "all coverage" is not estimable.

---

### Epic: Editor Crashes

**Description:** Reducing or eliminating editor crash scenarios.

#### Open Questions
1. Are there specific known crash scenarios already documented, or is this a general stability initiative?
2. Is there crash reporting/telemetry in place to identify the most frequent crashes?
3. What is the user impact when a crash occurs — is data lost, or does auto-save protect users?

#### Risks
- Without a defined list of crashes to fix, this cannot be estimated — it's a category, not a feature.

#### Decisions That Would Help
- Prioritizing the top 3 crash scenarios by frequency would convert this into an estimable and plannable item.

---

### Epic: Classes Reliability

**Description:** Fix known performance and reliability issues with classes loading in the editor and on the frontend.

#### Open Questions
1. Is there a defined threshold for "reliable" — e.g., what number of classes should the editor handle without performance degradation?
2. Is the issue primarily editor-side performance, frontend rendering, or both?
3. The comment notes bugs already scoped at L — is this a new initiative on top of those, or is it the same work?

#### Risks
- Existing bugs already scoped at L suggests this is likely a large effort regardless of the estimate shown.
- Performance work without defined success criteria (target metrics) is hard to call "done."

#### Decisions That Would Help
- Defining a measurable success criterion (e.g., "supports 500 classes with no visible lag on editor load") would make this estimable and testable.

---

### Epic: Components Performance

**Description:** Reduce enter/exit edit mode time and eliminate screen flickering.

#### Open Questions
1. Is there a target time for entering and exiting edit mode (e.g., under 500ms)?
2. Is the flickering issue reproducible on specific components, or universal?
3. What is the baseline today — has it been measured?

#### Risks
- Performance work without a defined baseline and target is difficult to scope — "faster" is not a done condition.

#### Decisions That Would Help
- Agreeing on a measurable target (e.g., enter edit mode in under X ms on a standard test page) is the prerequisite for estimating this.

---

## Category: Ecosystem Readiness

---

### Epic: Popular 3rd Party Add-ons — API Compatibility

**Description:** Ensure popular add-ons (CrocoBlock/JetEngine identified) work with v4 elements.

#### Open Questions
1. Which add-ons are explicitly in scope for this quarter?
2. Is the goal to fix compatibility reactively (patching our side to work with their code) or proactively (providing an API they can adopt)?
3. Do we have existing relationships with these add-on developers that can facilitate collaboration?
4. What does "work" mean — full feature parity with v3 behavior, or core functionality only?

#### Risks
- Fixing compatibility without a stable API means the same fixes may need to be repeated as both sides evolve.
- Third-party timelines are not in our control — a partner may not implement our API in time even if we provide it.

#### Decisions That Would Help
- Deciding between a reactive fix vs. a proactive API approach changes both the effort and the long-term strategy. This is a product/business decision.

---

### Epic: 3rd Party Developer Documentation

**Description:** Provide documentation that unblocks third-party developers waiting on Elementor.

#### Open Questions
1. What specific documentation is missing and blocking developers — API reference, migration guides, integration tutorials?
2. Who will own writing the documentation — product, engineering, or a dedicated content team?
3. Are there specific developers or partners with a committed timeline expectation?
4. Is there existing documentation that needs to be updated, or is it being created from scratch?

#### Risks
- If engineering is expected to write documentation without dedicated time budgeted for it, it will be deprioritized under delivery pressure.
- Incomplete or inaccurate documentation can create more support burden than no documentation.

#### Decisions That Would Help
- Assigning a clear owner (not "engineering in spare time") is the most important decision before this is planned.

---

### Epic: Extend Classes and Variables Functionality for 3rd Parties

**Description:** Allow third-party developers to extend the classes and variables system.

#### Open Questions
1. What specific extensions are third-party developers requesting — hooks, filters, API endpoints, UI injection points?
2. Is there existing research from developer interviews (mentioned elsewhere in the roadmap) that defines these needs?
3. Is this about backend API extensibility, UI extensibility, or both?

#### Risks
- Exposing stable extension APIs creates long-term backward compatibility commitments — changes later become breaking changes.
- Building this without direct input from target developers risks solving the wrong problem.

#### Decisions That Would Help
- Committing to interview 2–3 specific add-on developers before scoping this would significantly reduce the risk of building the wrong API.

---

## Category: UX

---

### Epic: Style Panel Logic Improvement

**Description:** Show most-used tabs at the top, add search, and collapse/expand tabs contextually per element type.

#### Open Questions
1. How is "most used" determined — global product analytics, per-user behavior, or per-element type?
2. Should personalized tab ordering persist per user, or reset per session?
3. Is there an existing design for this, or does UX need to create one first?

#### Risks
- Personalization based on individual user behavior adds infrastructure (data storage, logic) that would move this estimate up.

#### Decisions That Would Help
- Using element-type-based defaults (not per-user personalization) for the first iteration keeps this scoped and estimable.

---

### Epic: Re-design Spacing Control

**Description:** Redesign the spacing control to be visually logical, with Top/Bottom/Left/Right clearly represented.

#### Open Questions
1. Is there an existing design or mockup for the new control?
2. Should the redesign change behavior (e.g., how linked/unlinked values work) or only the visual presentation?
3. Does this need to apply consistently across all breakpoints?

#### Risks
- A visual-only redesign is low risk. A behavioral redesign that changes how users interact with spacing could cause confusion for existing users.

#### Decisions That Would Help
- Confirming this is a visual/UX change only (no behavioral change) vs. a functional redesign locks the estimate clearly.

---

### Epic: Remove Random V3 Containers in V4 Context

**Description:** Replace system-inserted v3 containers (in grids, layout CTAs on canvas, etc.) with v4 flexbox equivalents.

#### Open Questions
1. Is there a complete list of all locations where v3 containers currently appear in v4 contexts?
2. When the replacement happens, is it automatic (on-the-fly conversion) or does the user need to take an action?
3. What is the expected experience for existing sites that already contain these v3 containers — are they migrated, or do they remain as-is?

#### Risks
- Automatic replacement on existing sites could silently change layouts — a significant trust risk.
- Without a complete inventory of affected locations, the estimate is unreliable.

#### Decisions That Would Help
- Deciding whether existing sites are affected (migration) or only new placements going forward changes the effort considerably.

---

### Epic: Preview Class Style Before Applying

**Description:** Show users a live preview of a class's styles before they apply it to an element.

#### Open Questions
1. Where does the preview appear — a hover tooltip, an inline canvas preview, or a dedicated preview mode?
2. What happens when the class being previewed would conflict with existing styles on the element?
3. Should the preview work for both applying a new class and switching between existing classes?

#### Decisions That Would Help
- A hover tooltip preview (simpler) vs. a live canvas preview (more complex) is a key UX decision that directly determines the effort.

---

### Epic: Accept Color Values Without # Prefix

**Description:** Allow users to type hex color values without needing to prefix them with `#`.

#### Open Questions
1. Should this also support other color input formats (rgb, hsl, named colors)?
2. After input, should values be normalized to a consistent internal format?

#### Risks
- Minimal risk — this is a small, well-scoped QoL improvement.

---

### Epic: Confusion Between V3 and V4 Elements

**Description:** Address user confusion when mixing v3 and v4 elements.

#### Open Questions
1. What is the primary source of confusion — visual appearance, naming, behavior differences, or all three?
2. Is the long-term plan to sunset v3 elements entirely? If so, on what timeline?
3. Should the solution prevent mixing v3 and v4 elements, or make the distinction clearer while allowing it?
4. Does this affect all users or primarily those migrating from v3?

#### Risks
- If the solution is to restrict mixing, existing sites that already mix v3 and v4 elements could break.
- The long-term v3 sunset strategy directly determines how much investment is appropriate here — solving confusion in a way that becomes irrelevant once v3 is removed is wasted effort.

#### Decisions That Would Help
- The most important product decision here: is v3 being sunset, and when? That answer defines whether the right solution is "clear differentiation" or "active migration away from v3."

---

## Category: Design System Management at Scale

---

### Epic: Duplicate Classes

**Description:** Allow users to duplicate a class definition.

#### Open Questions
1. Does "duplicate" mean creating an independent copy of a class's styles, or a linked alias?
2. Where is the duplicate action surfaced — in the class manager, in the element panel, or both?
3. Should duplicated classes include all style properties or allow selective copying?

---

### Epic: Group Classes

**Description:** Allow users to organize classes into groups.

#### Open Questions
1. Is grouping hierarchical (nested groups) or flat (one level)?
2. Is this the same grouping system as Variables — Groups, or do classes and variables have separate group management?
3. Does grouping affect how classes are applied to elements, or is it purely for organization in the manager?

#### Decisions That Would Help
- Aligning the grouping model for both classes and variables (same UX pattern) would reduce design and implementation effort.

---

### Epic: Undo / Redo for Class Changes

**Description:** Extend undo/redo support to cover class edits and potentially other design system changes.

#### Open Questions
1. What class actions are currently not undoable — style edits, renames, deletions, group moves?
2. What does "and more" refer to specifically — is this scoped to classes, or does it include variables and other design system changes?
3. Should undo/redo history persist between sessions, or reset when the editor is closed?
4. Is there a maximum history depth required?

#### Risks
- Undo/redo for design system changes (classes, variables) is architecturally different from undo/redo for canvas changes — this is likely a significant infrastructure effort, which the L estimate may reflect.
- "And more" without definition means this estimate could be much larger than L.

#### Decisions That Would Help
- Scoping explicitly to class style edits only (not variables, not renames) for the first iteration would validate the L estimate and allow a reliable plan.

---

## Summary of Highest-Risk Epics

| Epic | Risk | Blocker |
|---|---|---|
| New Query Mechanism | Foundational dependency for multiple other epics | Define supported data sources for v1 |
| Real v4 Flexbox | XL with unclear scope, potential for breaking sites | Define specific missing capabilities |
| CSS Value Bugs | Not a scoped epic — it's a category | Requires a prioritized bug list before estimation |
| Layouts (×3) | Three epics with the same name and overlapping scope | Requires explicit differentiation between all three |
| V3/V4 Confusion | Solution depends entirely on v3 sunset strategy | Requires a product decision on v3 timeline |
| Variables Responsiveness | Architectural impact underestimated; estimate is "???" | Requires scope definition before estimation |
| Icons Library | XL estimate with no clear build vs. integrate decision | Single decision changes scope dramatically |
| Undo/Redo for Classes | "And more" makes this unbounded | Requires explicit scope list |
| Editor Crashes | Cannot be estimated without a bug list | Requires crash frequency data |

---

*Generated: March 2026*

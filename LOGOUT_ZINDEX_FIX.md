# Logout Button Z-Index Fix

## Issue
The logout button dropdown was appearing behind the "What's on your mind" post card, making it inaccessible.

## Root Cause
The CSS had a stacking context conflict:
- All `.container-xl`, `header`, and `main` elements were set to `z-index: 2`
- The topbar had `z-index: 1000`, but since it was inside a `.container-xl` element with `z-index: 2`, its z-index was relative to that parent's stacking context
- This effectively made the topbar only at `z-index: 2` in the global context
- Post cards and other content were at the same level, causing overlap issues

## Solution
Separated the z-index layers properly in `assets/css/index.css`:

```css
/* Ensure content is above background effects and particles */
.container,
.container-xl,
aside {
  position: relative;
  z-index: 2;
}

/* Header should be above all content */
header {
  position: relative;
  z-index: 1001;
}

/* Main content below header but above background */
main {
  position: relative;
  z-index: 2;
}
```

Also added to `.card-post-form`:
```css
.card-post-form{
  /* ... existing styles ... */
  position: relative;
  z-index: 1;
}
```

## Z-Index Hierarchy (from bottom to top)
1. **Background effects** (`body::before`): `z-index: 0`
2. **PlayStation particles**: `z-index: 1`
3. **Post cards**: `z-index: 1` (card-post-form)
4. **Main content & containers**: `z-index: 2`
5. **Header/Topbar**: `z-index: 1001`
6. **Dropdown menus**: `z-index: 10001`

## Result
✅ The logout button dropdown now appears above all content
✅ Post cards stay below the header
✅ All interactive elements remain accessible
✅ Proper visual hierarchy maintained

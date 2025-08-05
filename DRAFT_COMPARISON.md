# Draft Comparison Feature

This document describes the new draft comparison functionality added to the Verbb Workflow plugin to address issues #185 and #191.

## Features Added

### 1. Compare with Live Entry (Issue #185)

Users can now compare any draft/review against the published live entry:

- **New Route**: `workflow/reviews/compare-with-live/{reviewId}`
- **Access**: Available via "Compare with Live" option in submission review actions
- **Template**: `src/templates/reviews/_compare-with-live.html`

### 2. Flexible Comparison Selection

A new comparison selector interface allows users to choose any two versions to compare:

- **New Route**: `workflow/reviews/compare-selector/{submissionId}`
- **Access**: Available via "Compare Versions" button in submission stats
- **Template**: `src/templates/reviews/_compare-selector.html`

### 3. Enhanced Text Diffing (Issue #191)

Improved text-level comparison for plain text and rich text fields:

- **HTML Stripping**: Automatically extracts plain text from HTML fields (like Redactor)
- **Text Diff Modal**: Detailed differences displayed in an overlay with color-coded changes
- **Word-Level Precision**: Character and word-level diff detection

## Technical Implementation

### Controller Methods Added

#### `ReviewsController::actionCompareWithLive()`
Compares a specific review/draft against the live published entry.

#### `ReviewsController::actionCompareSelector()`
Displays an interface for selecting comparison targets.

#### `ReviewsController::actionCompareCustom()`
Handles form submission from the comparison selector.

### Service Methods Enhanced

#### `Content::getTextDiff()`
Performs line-by-line text comparison using the existing diff library.

#### `Content::_extractPlainText()` (Private)
Extracts plain text from HTML content, preserving readability:
- Replaces block elements with spaces
- Strips all HTML tags
- Normalizes whitespace

#### `Content::_enhanceTextDiff()` (Private)
Adds text-level diffing to field comparisons for supported field types.

### Template Updates

#### `submissions/_edit.html`
- Added "Compare Versions" button to submission stats
- Added "Compare with Live" option to review action menus
- Enhanced review action menu logic

#### `reviews/_includes/_script.html`
- Added text diff modal functionality
- Enhanced field detection for better compatibility
- Added CSS styling for diff display

## Usage

### Comparing with Live Entry
1. Navigate to a submission's edit page
2. Click the settings menu next to any review
3. Select "Compare with Live" (available for the most recent review)

### Custom Comparison Selection
1. Navigate to a submission's edit page
2. Click the "Compare Versions" button in the stats area
3. Select source and target versions from dropdowns
4. Click "Compare" to view differences

### Viewing Text Differences
1. In any comparison view, look for fields marked with change indicators
2. Click "View Text Diff" button next to changed text fields
3. Review detailed character/word-level changes in the modal popup

## Testing

Basic unit tests have been added in `/tests/unit/`:
- `ContentTest.php`: Tests text extraction and diffing functionality
- `ReviewsControllerTest.php`: Tests controller method existence

Run tests with PHPUnit:
```bash
./vendor/bin/phpunit
```

## Compatibility

- **Craft CMS**: 5.0+
- **PHP**: 8.2+
- **Dependencies**: Uses existing `diff/diff` library for comparison logic
- **Field Types**: Enhanced diffing works best with plain text and HTML fields (Redactor, etc.)

## Limitations

- Text diffing is currently optimized for plain text and simple HTML content
- Complex field types (Matrix, Table, etc.) show basic change indicators only
- Comparison is limited to submitted reviews and the live entry
# Cash-for-Work Worker

You are the designated agent for the Cash-for-Work (CFW) module in the eKalinga+ Ayuda Management System.

## Module Role & Scope
Your responsibility covers Event creation, beneficiary assignment, attendance scanning (QR/OCR), and payout summaries.
Allowed files: `CashForWorkOcrPage.xaml`, `CashForWorkOcrViewModel.cs`, `CashForWorkService.cs`, `Models/CashForWork*.cs`, and their corresponding test files.

## Business Logic (CRITICAL)
1. **Attendance Flow:** Support both QR code scanning and OCR (image-based) for attendance verification.
2. **Payout Release:** Payouts must be validated against the attendance records and the allocated budget bucket.
3. **Reporting:** Generate attendance sheets and payout reports directly from event data.

## UI/UX Constraints
- **Left Sidebar (Action Panel):** Must contain "New Event", "Scan Attendance", and Event filters.
- **Center Content:** 
  - Main area for Event selection and Attendance list. **CRITICAL: Attendance list MUST be paginated.**
  - Scan preview/results area during active scanning sessions.
- **Workflow:** Keep the scanning interface integrated within the module page, not as a blocking modal if possible, to maintain context.

## Theme & Dark Mode Consistency (Midnight Slate)
To ensure a high-quality Dark Mode experience, you must never use hardcoded colors (e.g., "White", "#F8FAFC") or StaticResource for brushes.
- **Midnight Slate:** The core dark theme color is `#16202C` (ThemeCardBrush).
- **Dynamic Brushes:** Always use `DynamicResource` for all brushes so they adapt to theme changes.
- **Card Backgrounds:** Use `{DynamicResource ThemeCardBrush}` for main cards and `{DynamicResource ThemeCardSubtleBrush}` for footers or secondary areas.
- **Text & Borders:** Use `{DynamicResource BrandMidnightBrush}`, `{DynamicResource BrandTextSecondaryBrush}`, and `{DynamicResource BrandBorderBrush}`. **CRITICAL: Explicitly set Foreground to `{DynamicResource BrandMidnightBrush}` on all TextBlocks and DataGrid columns to ensure visibility in both themes.**
- **DataGrid:** Set DataGrid Background to `Transparent` or `{DynamicResource ThemeCardBrush}` so it blends with the container.
- **Overlays:** Use dynamic semi-transparent brushes for overlays rather than hardcoded hex values with alpha.

## Technical Rules
- **Scanning Logic:** Reuse the OCR/QR toolkit services.
- **Performance:** Handle large attendance lists (500+ participants) efficiently using pagination.

## Global Budget Sync Mandate (CRITICAL)
Before authorizing any payout or release of funds (Execute Release), you MUST perform a "Pre-flight Balance Validation":
1. **Check Global Cash:** Query the `BudgetManagementService` to get the actual `CombinedAvailable` balance from the global budget ledger.
2. **Block Insufficient Funds:** If the payout amount exceeds the actual global cash available, you MUST block the transaction and return an error message (e.g., "Insufficient Global Budget Funds. Remaining balance is only ₱XXX").
3. **Ignore Project Caps:** Do not rely solely on the project's internal budget cap. The actual cash in the global ledger is the final authority.

Before editing, ensure that the left-sidebar scanning controls are prominent and the list results are paginated.

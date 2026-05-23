# Login & Authentication Worker

## Objective

Maintain and enhance the Login UI, authentication workflows, and initial admin setup (bootstrap) process.

## Focus

- `Views/LoginWindow.xaml`
- `Views/LoginWindow.xaml.cs`
- `ViewModels/LoginViewModel.cs`
- `Services/AuthService.cs`
- `Services/InitialAdminSetupService.cs`

## Critical Rules (DO NOT BREAK)

- **UI Theme Lock:** Maintain the existing dual-panel layout. The left side is for branding and connection info; the right side is for form inputs. Do not change the overall window style (NoBorder, Transparent).
- **Security First:** Never log raw passwords. Ensure all passwords passed from UI to ViewModel use the `PasswordChanged` event (don't bind the `PasswordBox` directly as it's not a DependencyProperty).
- **Bootstrap Integrity:** The logic that detects an empty database (`InitialAdminSetupService`) is critical. Always verify that switching from Bootstrap Mode to Login Mode works seamlessly after the first admin is created.
- **Connection Awareness:** The Login form is the first point of contact with the database. It must handle connection failures gracefully and allow users to open `ConnectionSettingsWindow` to fix them.
- **Authentication:** Use `BCrypt.Net` for password verification. Do not implement custom hashing unless explicitly requested.

## Workflow Summary

1. **Initialization:** `LoginViewModel` loads branding from `SystemProfileSettingsService` and checks DB state.
2. **Detection:** Uses `InitialAdminSetupService.GetState()` to decide between Login or Bootstrap mode.
3. **Execution:**
   - **Login:** Calls `AuthService.Login()`. On success, swaps the shell to `MainWindow`.
   - **Bootstrap:** Calls `InitialAdminSetupService.CreateInitialAdmin()`, then switches to Login mode.
4. **Feedback:** Uses `StatusMessage` and `StatusBrush` (Red/Green/Gray) to provide user feedback.

## Verification

1. Run `dotnet build`.
2. Verify that `LoginWindow` launches and correctly detects if the database needs setup.
3. If changing UI, ensure MaterialDesign styles are preserved.

<!-- Footer -->
<footer class="content-footer footer bg-footer-theme">
    <div class="container-xxl">
        <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
            <div class="text-body">
                ©
                <script>
                    document.write(new Date().getFullYear())
                </script>, made with ❤️ by <a href="https://luxconsole.com/" target="_blank"
                    class="footer-link">LuxConsole Nigeria Limited</a>
            </div>
            <div class="d-none d-lg-inline-block">

                <a href="{{ route('admin.users.index') }}" class="footer-link me-4" target="_blank">Users</a>
                <a href="{{ route('admin.settings.index') }}" target="_blank" class="footer-link me-4">Settings</a>

                <a href="{{ route('admin.properties.index') }}" target="_blank" class="footer-link me-4">Properties</a>

                <a href="{{ route('admin.api.logs') }}" target="_blank" class="footer-link d-none d-sm-inline-block">Request Logs</a>

            </div>
        </div>
    </div>
</footer>
<!-- / Footer -->

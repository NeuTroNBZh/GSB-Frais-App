<script>
document.addEventListener('DOMContentLoaded', function () {
	var forms = document.querySelectorAll('form.js-confirm-action');

	forms.forEach(function (form) {
		form.addEventListener('submit', function (event) {
			var message = form.getAttribute('data-confirm-message') || 'Confirmer cette action ?';
			if (!window.confirm(message)) {
				event.preventDefault();
			}
		});
	});
});
</script>
</body>
</html>

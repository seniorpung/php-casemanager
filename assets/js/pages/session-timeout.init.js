
$.sessionTimeout({
	keepAliveUrl: 'index.php',
	logoutButton:'Logout',
	logoutUrl: 'auth-login.php',
	redirUrl: 'auth-lock-screen.php',
	warnAfter: 1500000,  // 25 minutes (25 * 60 * 1000)
    redirAfter: 300000,  //5 minutes (30 * 60 * 1000)
	countdownMessage: 'Redirecting in {timer} seconds.'
});

$('#session-timeout-dialog  [data-dismiss=modal]').attr("data-bs-dismiss", "modal");
document.querySelector(".logout-button").addEventListener("click", async function() {
    try {
        // Remover JWT e nome de usuário do localForage
        await localforage.removeItem("jwt");
        await localforage.removeItem("username");
        console.log("Logout realizado com sucesso. Dados removidos.");

        // Redirecionar para a tela de login
        document.getElementById("dashboardContainer").style.display = "none";
        document.getElementById("loginContainer").style.display = "block";
        document.getElementById("loginMessage").textContent = "Você saiu com sucesso!";
    } catch (error) {
        console.log("Erro ao realizar logout:", error);
    }
});
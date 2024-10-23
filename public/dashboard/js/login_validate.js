localforage.getItem("jwt").then(function (jwt) {
  if (jwt) {
    document.getElementById("loginContainer").style.display = "none";
    document.getElementById("dashboardContainer").style.display = "block";
    console.log("Usuário está logado, exibindo dashboard.");
  } else {
    document.getElementById("loginContainer").style.display = "block";
    document.getElementById("dashboardContainer").style.display = "none";
    console.log("Usuário não está logado, exibindo formulário de login.");
  }
});



document
  .getElementById("loginForm")
  .addEventListener("submit", async function (event) {
    event.preventDefault();

    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    if (username === "" || password === "") {
      document.getElementById("loginMessage").textContent =
        "Por favor, preencha todos os campos.";
      console.log("Campos de usuário ou senha estão vazios.");
      return;
    }

    console.log("Iniciando requisição de login...");

    try {
      const response = await fetch("http://localhost:8000/public/backend/login.php", {
        method: "POST",
        body: JSON.stringify({
          username: username,
          password: password,
        }),
      });

      const data = await response.json();
      console.log("Login bem-sucedido, recebendo dados:", data);

      if (response.ok) {
        // Armazenar JWT e nome de usuário no LocalForage
        await localforage.setItem("jwt", data.jwt);
        console.log("JWT armazenado com sucesso!");

        await localforage.setItem("username", data.username);
        console.log("Nome de usuário armazenado com sucesso!");

        // Exibir dashboard e ocultar o formulário de login
        document.getElementById("loginContainer").style.display = "none";
        document.getElementById("dashboardContainer").style.display = "block";
        document.getElementById("loginMessage").textContent =
          "Login realizado com sucesso!";
      } else {
        document.getElementById("loginMessage").textContent =
          "Usuário ou senha incorretos!";
        console.log("Erro ao fazer login:", data);
      }
    } catch (error) {
      document.getElementById("loginMessage").textContent =
        "Ocorreu um erro ao tentar fazer login. Tente novamente mais tarde.";
      console.log("Erro ao fazer login:", error);
    }
  });


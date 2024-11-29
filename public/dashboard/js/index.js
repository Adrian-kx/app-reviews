async function fetchData() {
  const jwt = await localforage.getItem("jwt");
  console.log("JWT", jwt);
  try {
    if (!jwt) {
      throw new Error("JWT não encontrado");
    }
    const response = await fetch(
      "http://localhost:8000/public/backend/endpoints/get/responses.php",
      {
        method: "GET",
        headers: {
          Authorization: `Bearer ${jwt}`,
        },
      }
    );
    console.log("AQUI O DATAS", response);
    if (!response.ok) {
      if (response.status === 401) {
        throw new Error("Não autorizado. Verifique seu JWT.");
      }
      throw new Error("Erro ao buscar dados");
    }

    const data = await response.json();
    console.log("data", data);
    return data;
  } catch (error) {
    console.error("Erro ao carregar dados:", error);
    return [];
  }
}
document
  .getElementById("manageQuestionsButton")
  .addEventListener("click", function () {
    window.location.href = "/app-reviews/public/dashboard/add_question.php";
  });

// Função principal para inicializar o dashboard
async function initDashboard() {
  const datas = await fetchData();

  const setores = datas.map((item) => item.name);
  const medias = datas.map((item) => {
    const total = item.reviews.reduce((sum, review) => sum + review.note, 0);
    return (total / item.reviews.length).toFixed(2);
  });

  const ctxSetor = document.getElementById("setorChart").getContext("2d");
  new Chart(ctxSetor, {
    type: "bar",
    data: {
      labels: setores,
      datasets: [
        {
          label: "Média das Avaliações",
          data: medias,
          backgroundColor: "rgba(75, 192, 192, 0.2)",
          borderColor: "rgba(75, 192, 192, 1)",
          borderWidth: 1,
        },
      ],
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });

  const allNotas = datas.flatMap((item) =>
    item.reviews.map((review) => review.note)
  );
  const ctxGeral = document.getElementById("geralChart").getContext("2d");
  new Chart(ctxGeral, {
    type: "bar",
    data: {
      labels: ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10"],
      datasets: [
        {
          label: "Frequência das Notas",
          data: allNotas.reduce((acc, note) => {
            acc[note - 1] = (acc[note - 1] || 0) + 1;
            return acc;
          }, new Array(10).fill(0)),
          backgroundColor: "rgba(255, 159, 64, 0.2)",
          borderColor: "rgba(255, 159, 64, 1)",
          borderWidth: 1,
        },
      ],
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });

  const mediaTable = document.getElementById("mediaTable");
  setores.forEach((setor, index) => {
    const row = `<tr><td>${setor}</td><td>${medias[index]}</td></tr>`;
    mediaTable.innerHTML += row;
  });

  const setorFilter = document.getElementById("setorFilter");
  const setoresUnicos = [...new Set(datas.map((item) => item.name))];
  setoresUnicos.forEach((setor) => {
    const option = document.createElement("option");
    option.value = setor;
    option.textContent = setor;
    setorFilter.appendChild(option);
  });

  document.getElementById("filterButton").addEventListener("click", () => {
    preencherTabela(
      setorFilter.value,
      document.getElementById("sortNotas").value,
      document.getElementById("dateStart").value,
      document.getElementById("dateEnd").value
    );
  });

  preencherTabela();
}

function preencherTabela(
  filtroSetor = "",
  ordemNota = "desc",
  dataInicio = "",
  dataFim = ""
) {
  const filterTable = document.getElementById("filterTable");
  filterTable.innerHTML = "";

  const dataInicioObj = dataInicio ? new Date(dataInicio) : null;
  const dataFimObj = dataFim ? new Date(dataFim) : null;

  let filteredData = datas.flatMap((item) =>
    item.reviews.map((review) => ({
      setor: item.name,
      nota: review.note,
      data: review.date,
      texto: review.text,
    }))
  );

  if (filtroSetor) {
    filteredData = filteredData.filter(
      (review) => review.setor === filtroSetor
    );
  }

  if (dataInicioObj && dataFimObj) {
    filteredData = filteredData.filter((review) => {
      const reviewDate = new Date(review.data);
      return reviewDate >= dataInicioObj && reviewDate <= dataFimObj;
    });
  }

  filteredData.sort((a, b) =>
    ordemNota === "desc" ? b.nota - a.nota : a.nota - b.nota
  );

  filteredData.forEach((review) => {
    const row = `<tr>
            <td>${review.setor}</td>
            <td>${review.nota}</td>
            <td>${review.data}</td>
            <td>${review.texto}</td>
        </tr>`;
    filterTable.innerHTML += row;
  });
}

initDashboard();
async function checkLogin() {
  const jwt = await localforage.getItem("jwt");
  console.log("AQUIIII", jwt);
  if (jwt) {
    try {
      document.getElementById("loginContainer").style.display = "none";
      document.getElementById("dashboardContainer").style.display = "block";
      console.log("Usuário está logado, exibindo dashboard.");
    } catch (error) {
      console.log("Erro no JWT:", error);
      await attemptRefreshToken();
    }
  } else {
    document.getElementById("loginContainer").style.display = "block";
    document.getElementById("dashboardContainer").style.display = "none";
    console.log("Usuário não está logado, exibindo formulário de login.");
  }
}
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
      const response = await fetch(
        "http://localhost:8000/public/backend/login.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            username: username,
            password: password,
          }),
        }
      );

      const data = await response.json();
      console.log("Login bem-sucedido, recebendo dados:", data);
      console.log(JSON.stringify({ username, password }));

      if (response.ok) {
        await localforage.setItem("jwt", data.jwt);
        await localforage.setItem("refresh_token", data.refresh_token);
        console.log("Tokens armazenados com sucesso!");

        document.getElementById("loginContainer").style.display = "none";
        document.getElementById("dashboardContainer").style.display = "block";
        document.getElementById("loginMessage").textContent =
          "Login realizado com sucesso!";
      } else {
        document.getElementById("loginMessage").textContent =
          data.message || "Erro ao fazer login!";
        console.log("Erro ao fazer login:", data);
      }
    } catch (error) {
      document.getElementById("loginMessage").textContent =
        "Ocorreu um erro ao tentar fazer login. Tente novamente mais tarde.";
      console.log("Erro ao fazer login:", error);
    }
  });

document
  .querySelector(".logout-button")
  .addEventListener("click", async function () {
    try {
      await localforage.removeItem("jwt");
      await localforage.removeItem("refresh_token");
      console.log("Logout realizado com sucesso. Dados removidos.");

      document.getElementById("dashboardContainer").style.display = "none";
      document.getElementById("loginContainer").style.display = "block";
      document.getElementById("loginMessage").textContent =
        "Você saiu com sucesso!";
      initDashboard();
    } catch (error) {
      console.log("Erro ao realizar logout:", error);
    }
  });

async function attemptRefreshToken() {
  const refreshToken = await localforage.getItem("refresh_token");
  if (!refreshToken) {
    console.log("Nenhum Refresh Token encontrado, redirecionando ao login.");
    document.getElementById("loginContainer").style.display = "block";
    document.getElementById("dashboardContainer").style.display = "none";
    return;
  }

  try {
    const response = await fetch(
      "http://localhost:8000/public/backend/refresh.php",
      {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ refresh_token: refreshToken }),
      }
    );

    const data = await response.json();
    if (response.ok) {
      await localforage.setItem("jwt", data.jwt);
      console.log("Token renovado com sucesso!");
      document.getElementById("loginContainer").style.display = "none";
      document.getElementById("dashboardContainer").style.display = "block";
      initDashboard();
    } else {
      console.log("Erro ao renovar token:", data);
      document.getElementById("loginContainer").style.display = "block";
      document.getElementById("dashboardContainer").style.display = "none";
    }
  } catch (error) {
    console.log("Erro ao tentar renovar o token:", error);
    document.getElementById("loginContainer").style.display = "block";
    document.getElementById("dashboardContainer").style.display = "none";
  }
}

checkLogin();

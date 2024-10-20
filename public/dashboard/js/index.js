const datas = [{
    name: "Recepção",
    reviews: [{
            note: 8,
            date: "2024-10-01",
            text: "Moça da recepção muito simpática"
        },
        {
            note: 6,
            date: "2024-10-05",
            text: "Atendimento razoável, porém demorado."
        },
        {
            note: 9,
            date: "2024-10-12",
            text: "Equipe da recepção muito atenciosa e prestativa."
        }
    ]
},
{
    name: "Administração",
    reviews: [{
            note: 2,
            date: "2024-09-23",
            text: "Pessoal da gerência muito arrogante!"
        },
        {
            note: 4,
            date: "2024-10-06",
            text: "Processos burocráticos demorados e falta de comunicação clara."
        },
        {
            note: 5,
            date: "2024-10-10",
            text: "Administração precisa melhorar na eficiência e simpatia."
        },
        {
            note: 5,
            date: "2024-10-13",
            text: "Administração precisa melhorar na eficiência e simpatia."
        }
    ]
},
{
    name: "Enfermagem",
    reviews: [{
            note: 9,
            date: "2024-10-02",
            text: "Enfermeiras atenciosas e bem preparadas."
        },
        {
            note: 7,
            date: "2024-10-07",
            text: "Bom atendimento, mas faltam equipamentos."
        },
        {
            note: 8,
            date: "2024-10-11",
            text: "Equipe de enfermagem muito carinhosa e profissional."
        }
    ]
},
{
    name: "Médicos",
    reviews: [{
            note: 10,
            date: "2024-10-03",
            text: "Médicos altamente qualificados e atenciosos."
        },
        {
            note: 9,
            date: "2024-10-08",
            text: "Fui muito bem atendido, explicações detalhadas e claras."
        },
        {
            note: 7,
            date: "2024-10-14",
            text: "Bom atendimento, mas houve atraso na consulta."
        }
    ]
},
{
    name: "UTI",
    reviews: [{
            note: 9,
            date: "2024-10-04",
            text: "Cuidado excelente com os pacientes, ambiente bem equipado."
        },
        {
            note: 10,
            date: "2024-10-09",
            text: "Equipe da UTI salvou a vida do meu familiar, excelente cuidado."
        },
        {
            note: 8,
            date: "2024-10-15",
            text: "Boa estrutura, mas a comunicação com os familiares poderia ser melhor."
        }
    ]
},
{
    name: "Alimentação",
    reviews: [{
            note: 6,
            date: "2024-10-16",
            text: "A comida estava razoável, mas poderia ser mais variada."
        },
        {
            note: 5,
            date: "2024-10-11",
            text: "Serviço de alimentação lento e pouca variedade de pratos."
        },
        {
            note: 7,
            date: "2024-10-14",
            text: "Comida saudável e balanceada, mas com porções pequenas."
        }
    ]
}
];


const setores = datas.map(item => item.name);
const medias = datas.map(item => {
const total = item.reviews.reduce((sum, review) => sum + review.note, 0);
return (total / item.reviews.length).toFixed(2);
});
const ctxSetor = document.getElementById('setorChart').getContext('2d');
new Chart(ctxSetor, {
type: 'bar',
data: {
    labels: setores,
    datasets: [{
        label: 'Média das Avaliações',
        data: medias,
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1
    }]
},
options: {
    scales: {
        y: {
            beginAtZero: true
        }
    }
}
});

const allNotas = datas.flatMap(item => item.reviews.map(review => review.note));
const ctxGeral = document.getElementById('geralChart').getContext('2d');
new Chart(ctxGeral, {
type: 'bar',
data: {
    labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
    datasets: [{
        label: 'Frequência das Notas',
        data: allNotas.reduce((acc, note) => {
            acc[note - 1] = (acc[note - 1] || 0) + 1;
            return acc;
        }, new Array(10).fill(0)),
        backgroundColor: 'rgba(255, 159, 64, 0.2)',
        borderColor: 'rgba(255, 159, 64, 1)',
        borderWidth: 1
    }]
},
options: {
    scales: {
        y: {
            beginAtZero: true
        }
    }
}
});

const meses = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho"];
const mediaMensal = [7.5, 6.8, 7.9, 8.2, 7.1, 7.8];
const ctxMensal = document.getElementById('mensalChart').getContext('2d');
new Chart(ctxMensal, {
type: 'line',
data: {
    labels: meses,
    datasets: [{
        label: 'Média Mensal',
        data: mediaMensal,
        fill: false,
        borderColor: 'rgba(153, 102, 255, 1)',
        tension: 0.1
    }]
},
options: {
    scales: {
        y: {
            beginAtZero: true
        }
    }
}
});

const mediaTable = document.getElementById('mediaTable');
setores.forEach((setor, index) => {
const row = `<tr><td>${setor}</td><td>${medias[index]}</td></tr>`;
mediaTable.innerHTML += row;
});


const setorFilter = document.getElementById('setorFilter');
const setoresUnicos = [...new Set(datas.map(item => item.name))];
setoresUnicos.forEach(setor => {
const option = document.createElement('option');
option.value = setor;
option.textContent = setor;
setorFilter.appendChild(option);
});

function preencherTabela(filtroSetor = '', ordemNota = 'desc', dataInicio = '', dataFim = '') {
const filterTable = document.getElementById('filterTable');
filterTable.innerHTML = '';

// Transformar as datas para Date objetos (caso estejam preenchidas)
const dataInicioObj = dataInicio ? new Date(dataInicio) : null;
const dataFimObj = dataFim ? new Date(dataFim) : null;

let filteredData = datas.flatMap(item =>
    item.reviews.map(review => ({
        setor: item.name,
        nota: review.note,
        data: review.date,
        texto: review.text
    }))
);

if (filtroSetor) {
    filteredData = filteredData.filter(review => review.setor === filtroSetor);
}

if (dataInicioObj && dataFimObj) {
    console.log("aqui passou meu")
    filteredData = filteredData.filter(review => {
        const reviewDate = new Date(review.data);
        if (reviewDate >= dataInicioObj && reviewDate <= dataFimObj) {
            return `${reviewDate.getDate()}/${reviewDate.getMonth()}/${reviewDate.getFullYear()}`
        }
    });
}

filteredData.sort((a, b) => ordemNota === 'desc' ? b.nota - a.nota : a.nota - b.nota);

filteredData.forEach(review => {
    const row = `<tr>
<td>${review.setor}</td>
<td>${review.nota}</td>
<td>${review.data}</td>
<td>${review.texto}</td>
</tr>`;
    filterTable.innerHTML += row;
});
}


document.getElementById('filterButton').addEventListener('click', () => {
preencherTabela(
    setorFilter.value,
    document.getElementById('sortNotas').value,
    document.getElementById('dateStart').value,
    document.getElementById('dateEnd').value
);
});

preencherTabela();
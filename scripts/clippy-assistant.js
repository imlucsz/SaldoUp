const clippyFinancePhrases = [
    "Parece que você está organizando suas finanças! Quer começar ajustando o orçamento?",
    "Olá! Sou o Clippy Financeiro. Sim, ainda sugiro planilhas antes de qualquer compra.",
    "Eu ia sugerir um investimento de alto risco, mas descobri que seu perfil prefere dormir em paz.",
    "☕ Gastar R$ 15 em café todo dia parece pouco... até você multiplicar por 365.",
    "🔍 Analisando faturas... encontrei 3 assinaturas que você nem lembrava que tinha.",
    "💡 Dica: a regra dos 50/30/20 funciona melhor quando você sabe para onde vai o 50.",
    "🚀 Já criou sua reserva de emergência ou está contando com a sorte?",
    "🛒 Regra das 24 horas: espere um dia antes de comprar. Se ainda quiser, compre. (Geralmente passa).",
    "O cupom de desconto de R$ 10 não é economia se você gastou R$ 200 que não precisava.",
    "Reserva de emergência é igual seguro: você espera nunca usar, mas ama ter quando precisa.",
    "Desconto de 50% é ótimo. Não comprar nada é 100% de desconto.",
    "Seu orçamento passou no teste do mês. O teste da Black Friday ainda está pendente.",
    "O cartão de crédito não é extensão do salário. É só um intermediário impaciente.",
    "Rendimento de 100% do CDI é bom. Não parcelar a esmo é melhor ainda.",
    "Investir sem reserva de emergência é igual construir o telhado antes das paredes.",
    "Não gaste o dinheiro antes de receber. O futuro é incerto e o boleto é pontual.",
    "Pix parcelado: transformando um problema de hoje em 12 pequenos problemas do futuro.",
    "Pagar juros é financiar o sonho dos outros com o seu trabalho.",
    "A inflação não avisa quando vai subir. O seu planejamento financeiro deveria.",
    "Ações sobem e descem. O boleto do condomínio só conhece um sentido: para cima.",
    "Se o investimento promete rendimento garantido de 10% ao mês, a única garantia é a cilada.",
    "Diversificação: não coloque todos os ovos na mesma cesta (e nem todos no mesmo fundo).",
    "Renda fixa não é sem graça. Sem graça é ter que pedir emprestado no fim do mês.",
    "O melhor momento para começar a investir foi há 10 anos. O segundo melhor é hoje.",
    "Juros compostos: a oitava maravilha do mundo quando trabalham para você, um pesadelo na fatura.",
    "Se você não sabe para onde vai seu dinheiro, a fatura do cartão vai te contar no mês que vem.",
    "Modo escuro no app do banco ajuda a economizar bateria. Economizar dinheiro depende de você.",
    "Cartão de crédito é um excelente meio de pagamento, mas um péssimo empréstimo.",
    "Não economize o que sobra depois de gastar. Gaste o que sobra depois de economizar.",
    "O custo de vida tem o hábito estranho de subir exatamente no mesmo ritmo que o seu salário.",
    "Um orçamento não limita sua liberdade. Ele dá permissão para você gastar sem culpa.",
    "Fazer mercado com fome: a forma mais rápida de comprometer 20% do orçamento semanal.",
    "Comprar por impulso é trocar seu tempo de trabalho futuro por uma satisfação de 5 minutos.",
    "Renda passiva é incrível. O problema é que a renda ativa precisa trabalhar duro antes.",
    "Uma pausa para rever as metas financeiras também faz parte do ciclo de crescimento.",
    "Meu algoritmo prevê que você vai olhar a fatura do cartão agora. Confiança: 99,9%.",
    "Se o saldo piscar em vermelho, é alerta. Se o orçamento piscar, refaça as contas.",
    "Você clicou em mim. Finalmente uma consulta financeira que não cobra taxa de administração!",
    "Estou aqui para ajudar. Ou para lembrar suavemente que parcelar em 12x ainda é pagar.",
    "Eu ia contar uma piada sobre inflação, mas ela perdeu o poder de compra no caminho.",
    "Quantos investidores para mudar uma lâmpada? Depende: é renda fixa ou taxa variável?",
    "O parcelamento era simples. O acúmulo de parcelas no mês que vem é que vai ser longo.",
    "Lembrete: anotação de gastos no bloco de notas não é vergonha. É controle financeiro com pé no chão.",
    "A melhor meta financeira é aquela que você cumpre. A segunda melhor é a que você revisa sem drama.",
    "Seu próximo aporte pode esperar cinco minutos. A análise de gastos, talvez não."
];

function getRandomClippyFinancePhrase(currentPhrase) {
    const alternatives = clippyFinancePhrases.filter((phrase) => phrase !== currentPhrase);
    return alternatives[Math.floor(Math.random() * alternatives.length)];
}

(() => {
    const widget = document.querySelector('#clippy-widget');
    const bubble = document.querySelector('#clippy-bubble');
    const toggle = document.querySelector('#clippy-toggle');
    const close = document.querySelector('#clippy-close');
    const phraseElement = document.querySelector('#clippy-phrase');
    if (!widget || !bubble || !toggle || !close || !phraseElement) return;

    let phrase = clippyFinancePhrases[0];
    let intervalId;

    const changePhrase = () => {
        phrase = getRandomClippyFinancePhrase(phrase);
        phraseElement.classList.add('is-changing');
        window.setTimeout(() => {
            phraseElement.textContent = phrase;
            phraseElement.classList.remove('is-changing');
        }, 120);
    };

    const setOpen = (isOpen) => {
        bubble.hidden = !isOpen;
        toggle.setAttribute('aria-expanded', String(isOpen));
        toggle.setAttribute('aria-label', isOpen ? 'Fechar Clippy' : 'Abrir Clippy');
        window.clearInterval(intervalId);
        if (isOpen) intervalId = window.setInterval(changePhrase, 10000);
    };

    phraseElement.textContent = phrase;
    toggle.addEventListener('click', () => {
        changePhrase();
        setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });
    close.addEventListener('click', () => setOpen(false));
    widget.addEventListener('mouseenter', changePhrase);
    bubble.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });
})();

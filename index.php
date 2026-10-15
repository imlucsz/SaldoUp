<?php
require_once 'auth.php';
require_login();
require_once 'conexao.php';

$transactions = [];
$incomeTotal = 0.0;
$expenseTotal = 0.0;
$generalTotal = 0.0;
$loadError = null;

try {
    $stmt = $pdo->prepare('SELECT id, descricao, valor, data, categoria FROM transacoes WHERE usuario_id = ? ORDER BY id DESC');
    $stmt->execute([(int) $_SESSION['usuario_id']]);
    $transactions = $stmt->fetchAll();

    foreach ($transactions as $transaction) {
        $value = (float) $transaction['valor'];
        if ($value > 0) {
            $incomeTotal += $value;
        } elseif ($value < 0) {
            $expenseTotal += abs($value);
        }
        $generalTotal += $value;
    }
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    $loadError = 'Não foi possível carregar as transações. Tente novamente mais tarde.';
}

$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$formatCurrency = static function (float $value): string {
    return 'R$ ' . number_format($value, 2, ',', '.');
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="language" content="pt-BR">
    <title>SaldoUp</title>
    <link rel="shortcut icon" href="./assets/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="./styles/main.css">
    <link rel="stylesheet" href="./styles/responsivity.css">
    <link rel="stylesheet" href="./styles/index.css">
    <link rel="stylesheet" href="./styles/modal.css">
    <link rel="stylesheet" href="./styles/form-modal.css">
    <link rel="stylesheet" href="./styles/scrollbar.css">
    <link rel="stylesheet" href="./styles/animations.css">
    <link rel="stylesheet" href="./styles/clippy-assistant.css">
    <link rel="stylesheet" href="./styles/futuristic.css?v=3">
    <link rel="stylesheet" href="./styles/fintech.css?v=2">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <img src="./assets/logo.svg?v=2" alt="SaldoUp">
        <div class="account-controls">
            <span><?= $escape($_SESSION['usuario_nome'] ?? '') ?></span>
            <form action="logout.php" method="POST">
                <button type="submit" class="logout-button">Sair</button>
            </form>
        </div>
    </header>

    <main class="container">
        <section id="balance" class="database-balance" aria-label="Resumo financeiro">
            <div class="card summary-card summary-income">
                <h3><span>Entradas</span><img src="./assets/income.svg" alt=""></h3>
                <p><?= $escape($formatCurrency($incomeTotal)) ?></p>
            </div>
            <div class="card summary-card summary-expense">
                <h3><span>Saídas</span><img src="./assets/expense.svg" alt=""></h3>
                <p><?= $escape($formatCurrency($expenseTotal)) ?></p>
            </div>
            <div class="card summary-card total-balance <?= $generalTotal < 0 ? 'negative' : 'positive' ?>">
                <h3><span>Total geral</span><img src="./assets/total.svg" alt=""></h3>
                <p><?= $escape($formatCurrency($generalTotal)) ?></p>
            </div>
        </section>

        <section id="transaction">
            <div class="transaction-toolbar">
                <h2>Transações</h2>
                <div class="transaction-actions">
                    <button type="button" class="add-transaction-action" id="open-transaction-modal">+ Nova transação</button>
                    <button type="button" class="ai-analysis-button" id="analyze-transactions">
                        <img class="ai-analysis-icon" src="./public/Ia.botton.png" alt="" aria-hidden="true">
                        Análise com IA
                    </button>
                </div>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <p class="page-message error-message" role="alert"><?= $escape($_GET['error']) ?></p>
            <?php elseif (isset($_GET['deleted'])): ?>
                <p class="page-message success-message" role="status">Transação excluída.</p>
            <?php endif; ?>
            <?php if ($loadError !== null): ?>
                <p class="page-message error-message" role="alert"><?= $escape($loadError) ?></p>
            <?php endif; ?>

            <table id="data-table">
                <thead>
                    <tr>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th>Valor</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <?php $value = (float) $transaction['valor']; ?>
                        <tr>
                            <td class="description"><?= $escape($transaction['descricao']) ?></td>
                            <td class="category-cell"><span class="category-badge"><?= $escape($transaction['categoria']) ?></span></td>
                            <td class="<?= $value >= 0 ? 'income' : 'expense' ?>"><?= $escape($formatCurrency($value)) ?></td>
                            <td class="date"><?= $escape(date('d/m/Y', strtotime((string) $transaction['data']))) ?></td>
                            <td class="transaction-delete-cell">
                                <a class="delete-transaction" href="deletar.php?id=<?= rawurlencode((string) $transaction['id']) ?>" onclick="return confirm('Deseja excluir esta transação?')" aria-label="Excluir <?= $escape($transaction['descricao']) ?>">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($transactions === []): ?>
                        <tr><td class="empty-transactions" colspan="5">Nenhuma transação cadastrada.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <div id="create-transaction-modal" class="modal-overlay" aria-hidden="true">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="create-transaction-title">
            <div id="form">
                <h2 id="create-transaction-title">Nova transação</h2>
                <form class="transaction-form" action="salvar.php" method="POST">
                    <input type="hidden" name="redirect" value="1">
                    <div class="input-group form-field-full">
                        <label for="descricao">Descrição</label>
                        <input type="text" id="descricao" name="descricao" placeholder="Ex.: Mercado da semana" maxlength="255" required>
                    </div>
                    <div class="input-group form-field-full">
                        <label for="categoria">Categoria</label>
                        <select id="categoria" name="categoria" required>
                            <option value="💻 Tecnologia &amp; Dev">💻 Tecnologia &amp; Dev</option>
                            <option value="🍔 Alimentação">🍔 Alimentação</option>
                            <option value="🎉 Lazer &amp; Rolês">🎉 Lazer &amp; Rolês</option>
                            <option value="💊 Saúde &amp; Suplementos">💊 Saúde &amp; Suplementos</option>
                            <option value="📄 Contas &amp; Fixas">📄 Contas &amp; Fixas</option>
                            <option value="📌 Outros (padrão)" selected>📌 Outros (padrão)</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="valor">Valor</label>
                        <input type="number" id="valor" name="valor" placeholder="0,00" step="0.01" required>
                    </div>
                    <div class="input-group">
                        <label for="data">Data</label>
                        <input type="date" id="data" name="data" required>
                    </div>
                    <small class="help-for-modal">Use um valor negativo para registrar uma saída.</small>
                    <div class="input-group actions">
                        <button type="button" class="button cancel" id="close-transaction-modal">Cancelar</button>
                        <button type="submit">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="ai-analysis-modal" class="modal-overlay" aria-hidden="true">
        <div class="modal ai-analysis-card" role="dialog" aria-modal="true" aria-labelledby="ai-analysis-title">
            <button type="button" class="ai-analysis-close" id="close-ai-analysis" aria-label="Fechar análise">×</button>
            <h2 id="ai-analysis-title">Sua análise financeira</h2>
            <p id="ai-analysis-content" role="status" aria-live="polite"></p>
        </div>
    </div>

    <aside class="clippy-widget" id="clippy-widget">
        <section class="clippy-bubble" id="clippy-bubble" aria-labelledby="clippy-title" hidden>
            <div class="clippy-titlebar">
                <div class="clippy-title-left">
                    <span class="clippy-bulb" aria-hidden="true">💡</span>
                    <span class="clippy-title-text" id="clippy-title">Assistente financeiro</span>
                </div>
                <button type="button" class="clippy-close" id="clippy-close" aria-label="Fechar mensagem do Clippy">×</button>
            </div>
            <div class="clippy-content">
                <p class="clippy-main-text" id="clippy-phrase" aria-live="polite" aria-atomic="true"></p>
                <p class="clippy-prompt-text">Por onde começamos?</p>
                <nav class="clippy-options" aria-label="Atalhos financeiros">
                    <a href="#transaction" class="clippy-opt-btn">
                        <span class="clippy-bullet" aria-hidden="true"></span>
                        <span class="clippy-opt-text">Ver minhas transações</span>
                    </a>
                    <a href="#balance" class="clippy-opt-btn">
                        <span class="clippy-bullet" aria-hidden="true"></span>
                        <span class="clippy-opt-text">Conferir meu resumo</span>
                    </a>
                </nav>
            </div>
            <div class="clippy-tail" aria-hidden="true"></div>
        </section>
        <button type="button" class="clippy-sprite" id="clippy-toggle" aria-label="Abrir Clippy" aria-expanded="false" aria-controls="clippy-bubble">
            <svg viewBox="0 0 100 130" class="clippy-svg" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <defs>
                    <linearGradient id="clippy-metal-base" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#4a5568"/>
                        <stop offset="30%" stop-color="#ffffff"/>
                        <stop offset="55%" stop-color="#cbd5e1"/>
                        <stop offset="85%" stop-color="#64748b"/>
                        <stop offset="100%" stop-color="#334155"/>
                    </linearGradient>
                </defs>
                <ellipse cx="50" cy="120" rx="35" ry="6" fill="rgba(0, 0, 0, 0.25)"/>
                <path d="M 34,92 A 11,11 0 0,0 56,92 V 55 A 6,6 0 0,0 44,55 V 105 A 14,14 0 0,0 72,105 V 38 A 22,22 0 0,0 28,38 V 100" fill="none" stroke="#1a202c" stroke-width="10" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M 34,92 A 11,11 0 0,0 56,92 V 55 A 6,6 0 0,0 44,55 V 105 A 14,14 0 0,0 72,105 V 38 A 22,22 0 0,0 28,38 V 100" fill="none" stroke="url(#clippy-metal-base)" stroke-width="6.5" stroke-linecap="round" stroke-linejoin="round"/>
                <g class="clippy-eye clippy-eye-left">
                    <ellipse cx="43" cy="44" rx="10" ry="12.5" fill="#ffffff" stroke="#111827" stroke-width="2.5"/>
                    <ellipse cx="45" cy="45" rx="5" ry="6.5" fill="#111827"/>
                    <circle cx="43" cy="42" r="1.8" fill="#ffffff"/>
                </g>
                <g class="clippy-eye clippy-eye-right">
                    <ellipse cx="65" cy="42" rx="10" ry="12.5" fill="#ffffff" stroke="#111827" stroke-width="2.5"/>
                    <ellipse cx="63.5" cy="43.5" rx="5" ry="6.5" fill="#111827"/>
                    <circle cx="61.5" cy="40.5" r="1.8" fill="#ffffff"/>
                </g>
                <path class="clippy-brow clippy-brow-left" d="M 28 28 C 34 20, 48 22, 53 29" fill="none" stroke="#111827" stroke-width="5" stroke-linecap="round"/>
                <path class="clippy-brow clippy-brow-right" d="M 58 29 C 64 20, 76 21, 80 27" fill="none" stroke="#111827" stroke-width="5" stroke-linecap="round"/>
            </svg>
        </button>
    </aside>

    <footer><small>© <?= date('Y') ?> - SaldoUp</small></footer>
    <script>
        const transactionModal = document.querySelector('#create-transaction-modal');
        const analysisModal = document.querySelector('#ai-analysis-modal');
        const analysisButton = document.querySelector('#analyze-transactions');
        const analysisContent = document.querySelector('#ai-analysis-content');

        function setModalOpen(modal, isOpen) {
            modal.classList.toggle('active', isOpen);
            modal.setAttribute('aria-hidden', String(!isOpen));
        }

        function showAnalysisMessage(message) {
            analysisContent.classList.remove('is-typing');
            analysisContent.textContent = message;
            void analysisContent.offsetWidth;
            analysisContent.classList.add('is-typing');
        }

        document.querySelector('#open-transaction-modal').addEventListener('click', () => {
            setModalOpen(transactionModal, true);
        });
        document.querySelector('#close-transaction-modal').addEventListener('click', () => {
            setModalOpen(transactionModal, false);
        });
        document.querySelector('#close-ai-analysis').addEventListener('click', () => {
            setModalOpen(analysisModal, false);
        });
        analysisButton.addEventListener('click', async () => {
            setModalOpen(analysisModal, true);
            analysisContent.classList.remove('is-typing');
            analysisContent.textContent = 'Analisando suas transações...';
            analysisButton.disabled = true;

            try {
                const response = await fetch('ia_analise.php', {
                    headers: { 'Accept': 'application/json' }
                });
                const result = await response.json();
                if (!response.ok || result.status !== 'ok' || typeof result.dica !== 'string') {
                    throw new Error(result.dica || 'Não foi possível obter a análise agora.');
                }
                showAnalysisMessage(result.dica);
            } catch (error) {
                showAnalysisMessage(error.message || 'Não foi possível obter a análise agora.');
            } finally {
                analysisButton.disabled = false;
            }
        });
    </script>
    <script src="./scripts/clippy-assistant.js"></script>
</body>
</html>

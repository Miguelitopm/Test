<?php
require_once 'config/config.php';

if (!isLoggedIn()) {
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Obtener datos del wallet
$wallet_query = "SELECT wallet_balance FROM users WHERE id = :user_id";
$stmt = $db->prepare($wallet_query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$wallet_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener estadísticas
$stats_query = "SELECT 
                    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_earnings,
                    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_spent,
                    SUM(CASE WHEN type = 'recharge' THEN amount ELSE 0 END) as total_recharged
                FROM wallet_transactions WHERE user_id = :user_id";
$stmt = $db->prepare($stats_query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener transacciones recientes
$transactions_query = "SELECT * FROM wallet_transactions 
                       WHERE user_id = :user_id 
                       ORDER BY created_at DESC 
                       LIMIT 20";
$stmt = $db->prepare($transactions_query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Mi Wallet';
include 'includes/header.php';
?>

<div style="padding: 2rem 0;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h1 style="font-size: 2.5rem; font-weight: 900; margin-bottom: 3rem; color: var(--gray-800);">Mi Wallet</h1>

        <!-- Cards de balance -->
        <div class="grid grid-4 mb-8">
            <div class="card" style="background: linear-gradient(135deg, var(--primary-500), var(--primary-600)); color: white; border: none; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; right: 0; width: 6rem; height: 6rem; background: rgba(255, 255, 255, 0.1); border-radius: 50%; transform: translate(1.5rem, -1.5rem);"></div>
                <div class="card-content" style="position: relative; z-index: 10;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="font-size: 0.875rem; opacity: 0.9; font-weight: 500; margin-bottom: 0.5rem;">Saldo Disponible</p>
                            <p style="font-size: 2.5rem; font-weight: 900;"><?php echo formatPrice($wallet_data['wallet_balance']); ?></p>
                        </div>
                        <svg width="48" height="48" style="opacity: 0.8;" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-content">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="font-size: 0.875rem; color: var(--gray-600);">Total Ganado</p>
                            <p style="font-size: 1.5rem; font-weight: bold; color: var(--primary-600);"><?php echo formatPrice($stats['total_earnings'] ?: 0); ?></p>
                        </div>
                        <svg width="24" height="24" style="color: var(--secondary-600);" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-content">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="font-size: 0.875rem; color: var(--gray-600);">Total Gastado</p>
                            <p style="font-size: 1.5rem; font-weight: bold; color: var(--secondary-600);"><?php echo formatPrice($stats['total_spent'] ?: 0); ?></p>
                        </div>
                        <svg width="24" height="24" style="color: var(--secondary-600);" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-content">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="font-size: 0.875rem; color: var(--gray-600);">Total Recargado</p>
                            <p style="font-size: 1.5rem; font-weight: bold; color: var(--green-600);"><?php echo formatPrice($stats['total_recharged'] ?: 0); ?></p>
                        </div>
                        <svg width="24" height="24" style="color: var(--green-600);" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid" style="grid-template-columns: 1fr 2fr; gap: 2rem;">
            <!-- Recargar Wallet -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title" style="display: flex; align-items: center;">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20" style="margin-right: 0.5rem; color: var(--primary-600);">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        Recargar Wallet
                    </h2>
                </div>
                
                <div class="card-content">
                    <form id="rechargeForm" onsubmit="rechargeWallet(event)">
                        <div class="form-group">
                            <label for="amount" class="form-label">Monto a recargar</label>
                            <input type="number" id="amount" name="amount" class="form-input" 
                                   placeholder="0.00" min="1" max="10000" step="0.01" required>
                        </div>

                        <div style="margin-bottom: 1rem;">
                            <label class="form-label" style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 0.5rem; display: block;">Montos rápidos</label>
                            <div class="grid grid-3" style="gap: 0.5rem;">
                                <?php $quick_amounts = [10, 25, 50, 100, 250, 500]; ?>
                                <?php foreach ($quick_amounts as $amount): ?>
                                    <button type="button" onclick="setAmount(<?php echo $amount; ?>)" 
                                            class="btn" style="background: none; border: 1px solid var(--gray-300); font-size: 0.875rem; padding: 0.5rem;">
                                        $<?php echo $amount; ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; margin-bottom: 1rem;">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20" style="margin-right: 0.5rem;">
                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                            </svg>
                            Recargar con Tarjeta
                        </button>

                        <div style="font-size: 0.75rem; color: var(--gray-500); text-align: center;">
                            Métodos de pago seguros. Procesado por Stripe.
                        </div>
                    </form>
                </div>
            </div>

            <!-- Historial de Transacciones -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Historial de Transacciones</h2>
                </div>
                <div class="card-content">
                    <div style="margin-bottom: 1rem;">
                        <div style="display: flex; gap: 0.5rem;">
                            <button onclick="filterTransactions('all')" class="filter-btn active" data-filter="all">Todas</button>
                            <button onclick="filterTransactions('income')" class="filter-btn" data-filter="income">Ingresos</button>
                            <button onclick="filterTransactions('expense')" class="filter-btn" data-filter="expense">Gastos</button>
                            <button onclick="filterTransactions('recharge')" class="filter-btn" data-filter="recharge">Recargas</button>
                        </div>
                    </div>

                    <div id="transactionsList">
                        <?php foreach ($transactions as $transaction): ?>
                            <div class="transaction-item" data-type="<?php echo $transaction['type']; ?>" 
                                 style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; border: 1px solid var(--gray-200); border-radius: 0.5rem; margin-bottom: 0.5rem;">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div style="width: 2.5rem; height: 2.5rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; 
                                                <?php echo $transaction['type'] == 'income' ? 'background: var(--primary-100); color: var(--primary-600);' : 
                                                          ($transaction['type'] == 'expense' ? 'background: var(--secondary-100); color: var(--secondary-600);' : 
                                                           'background: var(--green-100); color: var(--green-600);'); ?>">
                                        <?php if ($transaction['type'] == 'income'): ?>
                                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        <?php elseif ($transaction['type'] == 'expense'): ?>
                                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        <?php else: ?>
                                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <p style="font-weight: 500; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($transaction['description']); ?></p>
                                        <p style="font-size: 0.875rem; color: var(--gray-500);"><?php echo timeAgo($transaction['created_at']); ?></p>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <p style="font-size: 1.125rem; font-weight: 600; 
                                              <?php echo $transaction['type'] == 'income' || $transaction['type'] == 'recharge' ? 'color: var(--primary-600);' : 'color: var(--secondary-600);'; ?>">
                                        <?php echo $transaction['type'] == 'income' || $transaction['type'] == 'recharge' ? '+' : '-'; ?><?php echo formatPrice($transaction['amount']); ?>
                                    </p>
                                    <span class="badge" style="font-size: 0.75rem; 
                                                                <?php echo $transaction['status'] == 'completed' ? 'background: var(--green-100); color: var(--green-800);' : 'background: var(--yellow-100); color: var(--yellow-800);'; ?>">
                                        <?php echo $transaction['status'] == 'completed' ? 'Completado' : 'Pendiente'; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (count($transactions) == 0): ?>
                            <div class="text-center" style="padding: 2rem 0;">
                                <svg width="48" height="48" style="color: var(--gray-300); margin: 0 auto 1rem;" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                    <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                </svg>
                                <h3 style="font-weight: 600; color: var(--gray-800); margin-bottom: 0.5rem;">No hay transacciones</h3>
                                <p style="color: var(--gray-600);">Tus transacciones aparecerán aquí</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.filter-btn {
    background: none;
    border: 1px solid var(--gray-300);
    color: var(--gray-700);
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.filter-btn:hover {
    background: var(--gray-50);
}

.filter-btn.active {
    background: var(--primary-500);
    color: white;
    border-color: var(--primary-500);
}
</style>

<script>
function setAmount(amount) {
    document.getElementById('amount').value = amount;
}

function rechargeWallet(event) {
    event.preventDefault();
    
    const amount = parseFloat(document.getElementById('amount').value);
    
    if (!amount || amount <= 0) {
        showAlert('Ingresa un monto válido', 'warning');
        return;
    }
    
    if (amount > 10000) {
        showAlert('Monto máximo: $10,000', 'warning');
        return;
    }
    
    if (confirm(`¿Confirmar recarga de ${formatPrice(amount)}?`)) {
        fetch('api/wallet.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'recharge',
                amount: amount
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Wallet recargado correctamente', 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showAlert(data.message || 'Error al recargar wallet', 'error');
            }
        })
        .catch(error => {
            showAlert('Error al recargar wallet', 'error');
        });
    }
}

function filterTransactions(type) {
    // Update active button
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-filter="${type}"]`).classList.add('active');
    
    // Filter transactions
    document.querySelectorAll('.transaction-item').forEach(item => {
        if (type === 'all' || item.dataset.type === type) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>

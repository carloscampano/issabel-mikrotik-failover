<style>
/* MikroTik Failover Module - Enterprise Professional UI */
:root {
    --primary-color: #2c3e50;
    --primary-light: #34495e;
    --accent-color: #3498db;
    --accent-hover: #2980b9;
    --success-color: #27ae60;
    --success-light: #d5f4e6;
    --danger-color: #e74c3c;
    --danger-light: #fdecea;
    --warning-color: #f39c12;
    --warning-light: #fef9e7;
    --gray-100: #f8f9fa;
    --gray-200: #e9ecef;
    --gray-300: #dee2e6;
    --gray-400: #ced4da;
    --gray-500: #868e96;
    --gray-600: #495057;
    --gray-800: #212529;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
    --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
    --shadow-lg: 0 8px 24px rgba(0,0,0,0.12);
    --radius-sm: 4px;
    --radius-md: 8px;
    --radius-lg: 12px;
    --transition: all 0.2s ease;
    --btn-height: 36px;
    --input-height: 36px;
}

/* Module Header */
.module-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
    border-radius: var(--radius-lg);
    padding: 28px 32px;
    margin-bottom: 28px;
    color: white;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}

.module-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}

.module-header::after {
    content: '';
    position: absolute;
    bottom: -60%;
    left: 20%;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.03);
    border-radius: 50%;
}

.module-header-content {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.module-title {
    margin: 0;
    font-size: 26px;
    font-weight: 600;
    letter-spacing: -0.5px;
    color: white !important;
}

.module-title i {
    margin-right: 12px;
    opacity: 0.9;
}

.module-subtitle {
    margin: 6px 0 0;
    opacity: 0.85;
    font-size: 14px;
    font-weight: 400;
    color: white !important;
}

.header-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

.header-stat {
    text-align: center;
    padding: 12px 20px;
    background: rgba(255,255,255,0.1);
    border-radius: var(--radius-md);
    backdrop-filter: blur(10px);
}

.header-stat-value {
    font-size: 28px;
    font-weight: 700;
    display: block;
}

.header-stat-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.8;
}

/* Status Panel */
.status-panel {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    margin-bottom: 28px;
    overflow: hidden;
}

.status-panel-header {
    background: var(--gray-100);
    padding: 20px 24px;
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
}

.status-panel-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--gray-800);
    display: flex;
    align-items: center;
    gap: 10px;
}

.status-panel-title i {
    color: var(--accent-color);
}

.status-panel-body {
    padding: 20px 24px;
}

.status-controls {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
}

.daemon-badge {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 500;
    gap: 8px;
}

.daemon-badge.running {
    background: var(--success-light);
    color: var(--success-color);
}

.daemon-badge.stopped {
    background: var(--danger-light);
    color: var(--danger-color);
}

.daemon-badge .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
}

.daemon-badge.running .dot {
    animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.8); }
}

.update-info {
    font-size: 12px;
    color: var(--gray-500);
}

.btn-refresh {
    background: none;
    border: 2px solid var(--gray-300);
    color: var(--gray-600);
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-refresh:hover {
    border-color: var(--accent-color);
    color: var(--accent-color);
    transform: rotate(90deg);
}

.btn-refresh.spinning i {
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Trunk Cards */
.trunk-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

@media (min-width: 640px) {
    .trunk-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1200px) {
    .trunk-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.trunk-card {
    background: white;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-md);
    padding: 18px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: var(--transition);
}

.trunk-card:hover {
    border-color: var(--accent-color);
    box-shadow: var(--shadow-sm);
    transform: translateY(-2px);
}

.trunk-info h4 {
    margin: 0 0 4px;
    font-size: 15px;
    font-weight: 600;
    color: var(--gray-800);
    display: flex;
    align-items: center;
    gap: 8px;
}

.trunk-info .ip {
    font-size: 13px;
    color: var(--gray-500);
    font-family: 'SF Mono', Monaco, 'Courier New', monospace;
}

.monitoring-tag {
    font-size: 10px;
    padding: 3px 8px;
    border-radius: 4px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.monitoring-tag.active {
    background: var(--success-light);
    color: var(--success-color);
}

.monitoring-tag.paused {
    background: var(--gray-200);
    color: var(--gray-600);
}

.trunk-status {
    display: flex;
    align-items: center;
    gap: 10px;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-badge.ok {
    background: var(--success-light);
    color: var(--success-color);
}

.status-badge.unreachable {
    background: var(--danger-light);
    color: var(--danger-color);
    animation: pulse-danger 1.5s ease-in-out infinite;
}

.status-badge.lagged {
    background: var(--warning-light);
    color: var(--warning-color);
}

.status-badge.unknown, .status-badge.not-found {
    background: var(--gray-200);
    color: var(--gray-600);
}

@keyframes pulse-danger {
    0%, 100% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.3); }
    50% { box-shadow: 0 0 0 8px rgba(231, 76, 60, 0); }
}

.latency {
    font-size: 11px;
    color: var(--gray-500);
}

/* Config Cards */
.config-section {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    margin-bottom: 24px;
}

@media (min-width: 992px) {
    .config-section {
        grid-template-columns: repeat(2, 1fr);
        gap: 28px;
    }
}

.config-card {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    transition: var(--transition);
}

.config-card:hover {
    box-shadow: var(--shadow-lg);
}

.config-card-header {
    background: linear-gradient(135deg, var(--gray-100) 0%, white 100%);
    padding: 18px 24px;
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    align-items: center;
    gap: 12px;
}

.config-card-header .icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.config-card-header .icon.mikrotik { background: #e8f4fc; color: #0096d6; }
.config-card-header .icon.smtp { background: #fef3e2; color: #f39c12; }
.config-card-header .icon.monitoring { background: #e8f8f0; color: #27ae60; }
.config-card-header .icon.daemon { background: #f3e8fd; color: #9b59b6; }
.config-card-header .icon.commands { background: #fee8e7; color: #e74c3c; }
.config-card-header .icon.trunks { background: #e8f4fc; color: #3498db; }

.config-card-header h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: var(--gray-800);
}

.config-card-body {
    padding: 20px 24px;
}

/* Form Styles - Enterprise Design */
.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-size: 12px;
    font-weight: 500;
    color: var(--gray-600);
    line-height: 1.4;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.form-group input[type="text"],
.form-group input[type="password"],
.form-group input[type="email"],
.form-group input[type="number"],
.form-group select {
    width: 100%;
    padding: 6px 10px;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-sm);
    font-size: 12px;
    line-height: 1.4;
    transition: var(--transition);
    background: white;
    height: 32px;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-group input[type="checkbox"] {
    margin-right: 8px;
    width: auto;
    height: auto;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

@media (min-width: 480px) {
    .form-row {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
}

/* Buttons - Enterprise Style */
.btn-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 16px;
}

.btn {
    padding: 6px 14px;
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border: none;
    min-height: 32px;
    white-space: nowrap;
    line-height: 1.4;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.btn:active {
    transform: translateY(0);
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
}

.btn-primary {
    background: var(--accent-color);
    color: white;
}

.btn-primary:hover {
    background: var(--accent-hover);
    box-shadow: 0 2px 4px rgba(52, 152, 219, 0.3);
}

.btn-secondary {
    background: white;
    color: var(--gray-600);
    border: 1px solid var(--gray-300);
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}

.btn-secondary:hover {
    background: var(--gray-100);
    border-color: var(--gray-400);
}

.btn-success {
    background: var(--success-color);
    color: white;
}

.btn-success:hover {
    background: #219a52;
    box-shadow: 0 2px 4px rgba(39, 174, 96, 0.3);
}

.btn-danger {
    background: var(--danger-color);
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
    box-shadow: 0 2px 4px rgba(231, 76, 60, 0.3);
}

.btn-sm {
    padding: 5px 10px;
    font-size: 11px;
    min-height: 26px;
    gap: 4px;
}

.btn-icon {
    width: 26px;
    height: 26px;
    min-height: 26px;
    min-width: 26px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-sm);
    flex-shrink: 0;
    text-align: center;
    vertical-align: middle;
    line-height: 26px;
    overflow: hidden;
    position: relative;
}

/* btn-sm no reduce el tamaño de btn-icon - mantiene 26x26 */
.btn-icon.btn-sm {
    width: 26px;
    height: 26px;
    min-height: 26px;
    min-width: 26px;
    line-height: 26px;
}

.btn i {
    font-size: 12px;
    line-height: 1;
    display: inline-block;
    vertical-align: middle;
}

.btn-sm i {
    font-size: 11px;
}

/* Íconos en botones de ícono - 14px para llenar el botón 26x26 */
.btn-icon i,
.btn-icon.btn-sm i,
.btn.btn-icon i {
    font-size: 14px !important;
    line-height: 1;
    margin: 0;
    padding: 0;
    display: block;
    text-align: center;
    /* Reset de estilos heredados que causan el rectángulo rojo */
    position: static !important;
    background: none !important;
    background-color: transparent !important;
    width: auto !important;
    height: auto !important;
}

/* Table Wrapper for horizontal scroll */
.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Tables - Enterprise Style */
.data-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 400px;
}

.data-table th {
    text-align: left;
    padding: 8px 10px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: var(--gray-500);
    border-bottom: 2px solid var(--gray-200);
    white-space: nowrap;
    background: var(--gray-100);
}

.data-table td {
    padding: 8px 10px;
    font-size: 12px;
    border-bottom: 1px solid var(--gray-200);
    vertical-align: middle;
}

.data-table tbody tr {
    transition: var(--transition);
}

.data-table tbody tr:hover {
    background: var(--gray-100);
}

.data-table tbody tr:last-child td {
    border-bottom: none;
}

.data-table code {
    background: var(--gray-200);
    padding: 4px 8px;
    border-radius: var(--radius-sm);
    font-size: 12px;
    color: var(--gray-800);
    font-family: 'SF Mono', Monaco, 'Courier New', monospace;
    display: inline-block;
    max-width: 280px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align: middle;
}

.data-table code:hover {
    white-space: normal;
    word-break: break-all;
    max-width: none;
}

/* Action buttons in tables - Enterprise Style */
.action-buttons {
    display: inline-flex;
    gap: 6px;
    justify-content: flex-end;
    align-items: center;
    flex-wrap: nowrap;
}

.action-buttons form {
    display: inline-flex;
    margin: 0;
    padding: 0;
    line-height: 0;
}

.action-buttons .btn {
    box-shadow: none;
    margin: 0;
}

.action-buttons .btn-icon {
    border: 1px solid var(--gray-300);
    background: white;
}

.action-buttons .btn-secondary.btn-icon {
    background: white;
    color: var(--gray-600);
}

.action-buttons .btn-secondary.btn-icon:hover {
    background: var(--gray-200);
    border-color: var(--gray-400);
}

.action-buttons .btn-danger.btn-icon {
    background: white;
    color: var(--danger-color);
    border-color: var(--danger-color);
}

.action-buttons .btn-danger.btn-icon:hover {
    background: var(--danger-color);
    color: white;
    border-color: var(--danger-color);
}

.action-buttons .btn-success.btn-icon {
    background: white;
    color: var(--success-color);
    border-color: var(--success-color);
}

.action-buttons .btn-success.btn-icon:hover {
    background: var(--success-color);
    color: white;
    border-color: var(--success-color);
}

.label {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.label-success {
    background: var(--success-light);
    color: var(--success-color);
}

.label-default {
    background: var(--gray-200);
    color: var(--gray-600);
}

/* Add Item Row - Enterprise Style */
.add-item-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px dashed var(--gray-300);
    align-items: center;
}

.add-item-row input,
.add-item-row select {
    flex: 1;
    min-width: 100px;
    padding: 5px 8px;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-sm);
    font-size: 12px;
    height: 28px;
    background: white;
    transition: var(--transition);
}

.add-item-row input:focus,
.add-item-row select:focus {
    outline: none;
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.add-item-row .btn {
    flex-shrink: 0;
    min-width: fit-content;
}

@media (max-width: 480px) {
    .add-item-row {
        flex-direction: column;
    }
    .add-item-row input,
    .add-item-row select {
        width: 100%;
        min-width: unset;
    }
    .add-item-row .btn {
        width: 100%;
    }
}

/* Daemon Control */
.daemon-control {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 20px;
    background: var(--gray-100);
    border-radius: var(--radius-md);
    margin-bottom: 16px;
}

.autostart-form {
    padding: 12px 20px;
    background: var(--gray-100);
    border-radius: var(--radius-md);
}

.autostart-form .checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 13px;
    color: var(--gray-600);
}

.autostart-form .checkbox-label input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
}

@media (max-width: 480px) {
    .daemon-control {
        flex-direction: column;
        text-align: center;
    }
    .daemon-control > div {
        width: 100%;
    }
    .daemon-control .btn {
        width: 100%;
    }
}

.daemon-status-info {
    display: flex;
    align-items: center;
    gap: 16px;
}

.daemon-status-info .status-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.daemon-status-info .status-icon.running {
    background: var(--success-light);
    color: var(--success-color);
}

.daemon-status-info .status-icon.stopped {
    background: var(--danger-light);
    color: var(--danger-color);
}

.daemon-status-info .status-text h4 {
    margin: 0 0 4px;
    font-size: 16px;
    color: var(--gray-800);
}

.daemon-status-info .status-text p {
    margin: 0;
    font-size: 13px;
    color: var(--gray-500);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 48px 24px;
    color: var(--gray-500);
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state p {
    margin: 0;
    font-size: 14px;
}

/* Responsive */
@media (max-width: 768px) {
    .module-header {
        padding: 20px 16px;
        margin-bottom: 20px;
    }

    .module-header-content {
        flex-direction: column;
        text-align: center;
        gap: 20px;
    }

    .module-title {
        font-size: 22px;
    }

    .header-stats {
        justify-content: center;
    }

    .header-stat {
        padding: 10px 16px;
    }

    .header-stat-value {
        font-size: 24px;
    }

    .status-panel-header {
        padding: 16px;
    }

    .status-panel-body {
        padding: 16px;
    }

    .trunk-grid {
        grid-template-columns: 1fr;
    }

    .trunk-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .trunk-status {
        width: 100%;
        justify-content: space-between;
    }

    .config-card-body {
        padding: 16px;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .btn-group {
        flex-direction: row;
        flex-wrap: wrap;
    }

    .btn-group .btn {
        flex: 1;
        min-width: 140px;
    }

    .data-table th,
    .data-table td {
        padding: 10px 12px;
    }
}
</style>

<!-- Module Header -->
<div class="module-header">
    <div class="module-header-content">
        <div>
            <h1 class="module-title"><i class="fa fa-shield"></i> MikroTik Failover</h1>
            <p class="module-subtitle">Sistema de Monitoreo y Recuperación Automática de Troncales SIP</p>
        </div>
        <div class="header-stats">
            <div class="header-stat">
                <span class="header-stat-value" id="totalTrunks">{$TRUNK_STATUS|@count}</span>
                <span class="header-stat-label">Troncales</span>
            </div>
            <div class="header-stat">
                <span class="header-stat-value" id="activeTrunks">{assign var="activeCount" value=0}{foreach from=$TRUNK_STATUS item=t}{if $t.status == 'OK'}{assign var="activeCount" value=$activeCount+1}{/if}{/foreach}{$activeCount}</span>
                <span class="header-stat-label">Activas</span>
            </div>
            <div class="header-stat">
                <span class="header-stat-value" id="commandCount">{$COMMANDS|@count}</span>
                <span class="header-stat-label">Comandos</span>
            </div>
        </div>
    </div>
</div>

<!-- Real-time Status Panel -->
<div class="status-panel">
    <div class="status-panel-header">
        <h2 class="status-panel-title">
            <i class="fa fa-heartbeat"></i>
            Estado en Tiempo Real
        </h2>
        <div class="status-controls">
            <div id="daemonBadge" class="daemon-badge {if $DAEMON_STATUS.status == 'running'}running{else}stopped{/if}">
                <span class="dot"></span>
                <span id="daemonText">Daemon {if $DAEMON_STATUS.status == 'running'}Activo{else}Detenido{/if}</span>
            </div>
            <span class="update-info">
                Actualizado: <span id="lastUpdate">{$smarty.now|date_format:"%H:%M:%S"}</span>
            </span>
            <button type="button" class="btn-refresh" id="refreshBtn" onclick="refreshStatus()" title="Actualizar">
                <i class="fa fa-refresh"></i>
            </button>
        </div>
    </div>
    <div class="status-panel-body">
        <div id="trunkStatusContainer" class="trunk-grid">
            {if count($TRUNK_STATUS) == 0}
            <div class="empty-state" style="grid-column: 1 / -1;">
                <i class="fa fa-plug"></i>
                <p>No hay troncales monitoreadas configuradas</p>
            </div>
            {else}
            {foreach from=$TRUNK_STATUS item=trunk}
            <div class="trunk-card" data-trunk-id="{$trunk.id}">
                <div class="trunk-info">
                    <h4>
                        {$trunk.name}
                        <span class="monitoring-tag {if $trunk.enabled}active{else}paused{/if}">
                            {if $trunk.enabled}Activo{else}Pausado{/if}
                        </span>
                    </h4>
                    <span class="ip"><i class="fa fa-globe"></i> {$trunk.ip}{if $trunk.port}:{$trunk.port}{/if}</span>
                </div>
                <div class="trunk-status">
                    <span class="status-badge {$trunk.status|lower|replace:'_':'-'}">
                        {if $trunk.status == 'OK'}<i class="fa fa-check"></i>{/if}
                        {if $trunk.status == 'UNREACHABLE'}<i class="fa fa-exclamation-triangle"></i>{/if}
                        {if $trunk.status == 'LAGGED'}<i class="fa fa-clock-o"></i>{/if}
                        {if $trunk.status == 'UNKNOWN'}<i class="fa fa-question"></i>{/if}
                        {if $trunk.status == 'NOT_FOUND'}<i class="fa fa-times"></i>{/if}
                        {$trunk.status}
                    </span>
                    {if $trunk.latency}
                    <span class="latency">{$trunk.latency}</span>
                    {/if}
                </div>
            </div>
            {/foreach}
            {/if}
        </div>
    </div>
</div>

<!-- Configuration Section -->
<div class="config-section">
    <!-- MikroTik Configuration -->
    <div class="config-card">
        <div class="config-card-header">
            <div class="icon mikrotik"><i class="fa fa-server"></i></div>
            <h3>{$LBL_MIKROTIK_CONFIG}</h3>
        </div>
        <div class="config-card-body">
            <form method="POST" name="form_mikrotik">
                <input type="hidden" name="menu" value="{$MODULE_NAME}">
                <input type="hidden" name="form_action" id="mikrotik_action" value="">
                <div class="form-row">
                    <div class="form-group">
                        <label>{$LBL_IP}</label>
                        <input type="text" name="mikrotik_ip" value="{$MIKROTIK_CONFIG.ip|default:''}" placeholder="192.168.1.1">
                    </div>
                    <div class="form-group">
                        <label>{$LBL_PORT}</label>
                        <input type="number" name="mikrotik_port" value="{$MIKROTIK_CONFIG.port|default:'22'}" placeholder="22">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>{$LBL_USER}</label>
                        <input type="text" name="mikrotik_user" value="{$MIKROTIK_CONFIG.user|default:''}" placeholder="admin">
                    </div>
                    <div class="form-group">
                        <label>{$LBL_PASSWORD}</label>
                        <input type="password" name="mikrotik_password" value="{$MIKROTIK_CONFIG.password|default:''}">
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary" onclick="document.getElementById('mikrotik_action').value='save_mikrotik'"><i class="fa fa-save"></i> {$LBL_SAVE}</button>
                    <button type="submit" class="btn btn-secondary" onclick="document.getElementById('mikrotik_action').value='test_mikrotik'"><i class="fa fa-plug"></i> {$LBL_TEST}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- SMTP Configuration -->
    <div class="config-card">
        <div class="config-card-header">
            <div class="icon smtp"><i class="fa fa-envelope"></i></div>
            <h3>{$LBL_SMTP_CONFIG}</h3>
        </div>
        <div class="config-card-body">
            <form method="POST" name="form_smtp">
                <input type="hidden" name="menu" value="{$MODULE_NAME}">
                <input type="hidden" name="form_action" id="smtp_action" value="">
                <div class="form-row">
                    <div class="form-group">
                        <label>{$LBL_SERVER}</label>
                        <input type="text" name="smtp_server" value="{$SMTP_CONFIG.server|default:''}" placeholder="smtp.gmail.com">
                    </div>
                    <div class="form-group">
                        <label>{$LBL_SMTP_PORT}</label>
                        <input type="number" name="smtp_port" value="{$SMTP_CONFIG.port|default:'465'}" placeholder="465">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>{$LBL_USER}</label>
                        <input type="text" name="smtp_user" value="{$SMTP_CONFIG.user|default:''}">
                    </div>
                    <div class="form-group">
                        <label>{$LBL_PASSWORD}</label>
                        <input type="password" name="smtp_password" value="{$SMTP_CONFIG.password|default:''}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>{$LBL_FROM_EMAIL}</label>
                        <input type="email" name="smtp_from" value="{$SMTP_CONFIG.from_email|default:''}">
                    </div>
                    <div class="form-group">
                        <label>{$LBL_TO_EMAIL}</label>
                        <input type="email" name="smtp_to" value="{$SMTP_CONFIG.to_email|default:''}">
                    </div>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="smtp_enabled" {if $SMTP_CONFIG.enabled}checked{/if}> {$LBL_ENABLED}</label>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary" onclick="document.getElementById('smtp_action').value='save_smtp'"><i class="fa fa-save"></i> {$LBL_SAVE}</button>
                    <button type="submit" class="btn btn-secondary" onclick="document.getElementById('smtp_action').value='test_smtp'"><i class="fa fa-paper-plane"></i> {$LBL_TEST_EMAIL}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Monitoring Configuration -->
    <div class="config-card">
        <div class="config-card-header">
            <div class="icon monitoring"><i class="fa fa-cogs"></i></div>
            <h3>{$LBL_MONITORING_CONFIG}</h3>
        </div>
        <div class="config-card-body">
            <form method="POST" name="form_monitoring">
                <input type="hidden" name="menu" value="{$MODULE_NAME}">
                <input type="hidden" name="save_monitoring" value="1">
                <div class="form-row">
                    <div class="form-group">
                        <label>{$LBL_DELAY_BEFORE_ACTION}</label>
                        <input type="number" name="delay_before_action" value="{$MONITORING_CONFIG.delay_before_action|default:'10'}" min="0">
                    </div>
                    <div class="form-group">
                        <label>{$LBL_COOLDOWN}</label>
                        <input type="number" name="cooldown_period" value="{$MONITORING_CONFIG.cooldown_period|default:'300'}" min="0">
                    </div>
                </div>
                <div class="form-group">
                    <label>{$LBL_RETRY_MODE}</label>
                    <select name="retry_mode" id="retry_mode" onchange="toggleRetryFields()">
                        <option value="single" {if $MONITORING_CONFIG.retry_mode == 'single'}selected{/if}>{$LBL_SINGLE}</option>
                        <option value="multiple" {if $MONITORING_CONFIG.retry_mode == 'multiple'}selected{/if}>{$LBL_MULTIPLE}</option>
                    </select>
                </div>
                <div id="retry_fields" style="{if $MONITORING_CONFIG.retry_mode != 'multiple'}display:none;{/if}">
                    <div class="form-row">
                        <div class="form-group">
                            <label>{$LBL_RETRY_COUNT}</label>
                            <input type="number" name="retry_count" value="{$MONITORING_CONFIG.retry_count|default:'3'}" min="1">
                        </div>
                        <div class="form-group">
                            <label>{$LBL_RETRY_INTERVAL}</label>
                            <input type="number" name="retry_interval" value="{$MONITORING_CONFIG.retry_interval|default:'30'}" min="1">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="monitoring_enabled" {if $MONITORING_CONFIG.enabled}checked{/if}> {$LBL_ENABLED}</label>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {$LBL_SAVE}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Daemon Control -->
    <div class="config-card">
        <div class="config-card-header">
            <div class="icon daemon"><i class="fa fa-play-circle"></i></div>
            <h3>{$LBL_DAEMON}</h3>
        </div>
        <div class="config-card-body">
            <form method="POST" name="form_daemon">
                <input type="hidden" name="menu" value="{$MODULE_NAME}">
                {if $DAEMON_STATUS.status == 'running'}
                <input type="hidden" name="stop_daemon" value="1">
                {else}
                <input type="hidden" name="start_daemon" value="1">
                {/if}
                <div class="daemon-control">
                    <div class="daemon-status-info">
                        <div class="status-icon {if $DAEMON_STATUS.status == 'running'}running{else}stopped{/if}">
                            {if $DAEMON_STATUS.status == 'running'}
                            <i class="fa fa-check"></i>
                            {else}
                            <i class="fa fa-times"></i>
                            {/if}
                        </div>
                        <div class="status-text">
                            <h4 id="daemonStatusLabel">
                                {if $DAEMON_STATUS.status == 'running'}{$LBL_RUNNING}{else}{$LBL_STOPPED}{/if}
                            </h4>
                            <p>{if $DAEMON_STATUS.pid}PID: <span id="daemonPid">{$DAEMON_STATUS.pid}</span>{else}Servicio no iniciado{/if}</p>
                        </div>
                    </div>
                    <div class="action-buttons">
                        {if $DAEMON_STATUS.status == 'running'}
                        <button type="submit" class="btn btn-danger btn-icon" title="{$LBL_STOP}"><i class="fa fa-stop"></i></button>
                        {else}
                        <button type="submit" class="btn btn-success btn-icon" title="{$LBL_START}"><i class="fa fa-play"></i></button>
                        {/if}
                    </div>
                </div>
            </form>
            <form method="POST" name="form_autostart" class="autostart-form">
                <input type="hidden" name="menu" value="{$MODULE_NAME}">
                <input type="hidden" name="toggle_autostart" value="1">
                <label class="checkbox-label">
                    <input type="checkbox" name="autostart_enabled" value="1" {if $DAEMON_ENABLED}checked{/if} onchange="this.form.submit()">
                    <span>{$LBL_AUTOSTART}</span>
                </label>
            </form>
        </div>
    </div>
</div>

<!-- Commands & Trunks Section -->
<div class="config-section">
    <!-- Commands -->
    <div class="config-card">
        <div class="config-card-header">
            <div class="icon commands"><i class="fa fa-terminal"></i></div>
            <h3>{$LBL_COMMANDS}</h3>
        </div>
        <div class="config-card-body">
            {if count($COMMANDS) > 0}
            <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">{$LBL_ORDER}</th>
                        <th>{$LBL_COMMAND}</th>
                        <th>{$LBL_DESCRIPTION}</th>
                        <th style="width: 90px;">{$LBL_STATUS}</th>
                        <th style="width: 60px;"></th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$COMMANDS item=cmd}
                    <tr>
                        <td><strong>{$cmd.order_num}</strong></td>
                        <td><code>{$cmd.command}</code></td>
                        <td>{$cmd.description}</td>
                        <td>
                            {if $cmd.enabled}
                            <span class="label label-success">{$LBL_ENABLED}</span>
                            {else}
                            <span class="label label-default">Off</span>
                            {/if}
                        </td>
                        <td>
                            <div class="action-buttons">
                                <form method="POST">
                                    <input type="hidden" name="menu" value="{$MODULE_NAME}">
                                    <input type="hidden" name="command_id" value="{$cmd.id}">
                                    <input type="hidden" name="delete_command" value="1">
                                    <button type="submit" class="btn btn-danger btn-icon btn-sm" onclick="return confirm('Eliminar comando?')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
            </div>
            {else}
            <div class="empty-state">
                <i class="fa fa-terminal"></i>
                <p>No hay comandos configurados</p>
            </div>
            {/if}
            <form method="POST" name="form_add_command">
                <input type="hidden" name="menu" value="{$MODULE_NAME}">
                <input type="hidden" name="add_command" value="1">
                <div class="add-item-row">
                    <input type="number" name="new_command_order" placeholder="#" style="width: 60px; flex: none;" value="0">
                    <input type="text" name="new_command" placeholder="Comando MikroTik">
                    <input type="text" name="new_command_desc" placeholder="Descripción">
                    <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-plus"></i></button>
                </div>
            </form>
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--gray-200);">
                <form method="POST" name="form_generate_script" style="display: inline;">
                    <input type="hidden" name="menu" value="{$MODULE_NAME}">
                    <input type="hidden" name="generate_script" value="1">
                    <button type="submit" class="btn btn-primary" onclick="return confirm('{$LBL_GENERATE_SCRIPT_CONFIRM}')">
                        <i class="fa fa-magic"></i> {$LBL_GENERATE_SCRIPT}
                    </button>
                </form>
                <small style="display: block; margin-top: 8px; color: var(--gray-600);">
                    {$LBL_GENERATE_SCRIPT_DESC}
                </small>
            </div>
        </div>
    </div>

    <!-- Monitored Trunks -->
    <div class="config-card">
        <div class="config-card-header">
            <div class="icon trunks"><i class="fa fa-phone"></i></div>
            <h3>{$LBL_TRUNKS}</h3>
        </div>
        <div class="config-card-body">
            {if count($MONITORED_TRUNKS) > 0}
            <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{$LBL_TRUNK_NAME}</th>
                        <th style="width: 100px;">{$LBL_STATUS}</th>
                        <th style="width: 120px;"></th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$MONITORED_TRUNKS item=trunk}
                    <tr>
                        <td><strong>{$trunk.trunk_name}</strong></td>
                        <td>
                            {if $trunk.enabled}
                            <span class="label label-success">{$LBL_ENABLED}</span>
                            {else}
                            <span class="label label-default">Off</span>
                            {/if}
                        </td>
                        <td>
                            <div class="action-buttons">
                                <form method="POST">
                                    <input type="hidden" name="menu" value="{$MODULE_NAME}">
                                    <input type="hidden" name="trunk_id" value="{$trunk.id}">
                                    <input type="hidden" name="toggle_trunk" value="1">
                                    <button type="submit" class="btn btn-secondary btn-icon btn-sm" title="Toggle">
                                        <i class="fa fa-toggle-{if $trunk.enabled}on{else}off{/if}"></i>
                                    </button>
                                </form>
                                <form method="POST">
                                    <input type="hidden" name="menu" value="{$MODULE_NAME}">
                                    <input type="hidden" name="trunk_id" value="{$trunk.id}">
                                    <input type="hidden" name="delete_trunk" value="1">
                                    <button type="submit" class="btn btn-danger btn-icon btn-sm" onclick="return confirm('Eliminar troncal?')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
            </div>
            {else}
            <div class="empty-state">
                <i class="fa fa-phone"></i>
                <p>No hay troncales monitoreadas</p>
            </div>
            {/if}
            <form method="POST" name="form_add_trunk">
                <input type="hidden" name="menu" value="{$MODULE_NAME}">
                <input type="hidden" name="add_trunk" value="1">
                <div class="add-item-row">
                    <select name="trunk_select" style="flex: 1;">
                        <option value="">{$LBL_SELECT_TRUNK}</option>
                        {foreach from=$AVAILABLE_TRUNKS item=t}
                        <option value="{$t}">{$t}</option>
                        {/foreach}
                    </select>
                    <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> {$LBL_ADD_TRUNK}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
var refreshInterval = null;
var moduleName = '{$MODULE_NAME}';

function toggleRetryFields() {
    var mode = document.getElementById('retry_mode').value;
    document.getElementById('retry_fields').style.display = mode === 'multiple' ? 'block' : 'none';
}

function getStatusClass(status) {
    var map = { 'OK': 'ok', 'UNREACHABLE': 'unreachable', 'LAGGED': 'lagged', 'UNKNOWN': 'unknown', 'NOT_FOUND': 'not-found' };
    return map[status] || 'unknown';
}

function getStatusIcon(status) {
    var icons = { 'OK': 'check', 'UNREACHABLE': 'exclamation-triangle', 'LAGGED': 'clock-o', 'UNKNOWN': 'question', 'NOT_FOUND': 'times' };
    return '<i class="fa fa-' + (icons[status] || 'question') + '"></i>';
}

function refreshStatus() {
    var btn = document.getElementById('refreshBtn');
    btn.classList.add('spinning');

    var xhr = new XMLHttpRequest();
    xhr.open('GET', '?menu=' + moduleName + '&action=get_status_ajax&_=' + Date.now(), true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            btn.classList.remove('spinning');
            if (xhr.status === 200) {
                try {
                    updateTrunkStatus(JSON.parse(xhr.responseText));
                } catch(e) { console.error('Parse error:', e); }
            }
        }
    };
    xhr.send();
}

function updateTrunkStatus(data) {
    document.getElementById('lastUpdate').textContent = data.timestamp;

    var badge = document.getElementById('daemonBadge');
    var text = document.getElementById('daemonText');
    if (data.daemon.status === 'running') {
        badge.className = 'daemon-badge running';
        text.textContent = 'Daemon Activo';
    } else {
        badge.className = 'daemon-badge stopped';
        text.textContent = 'Daemon Detenido';
    }

    var container = document.getElementById('trunkStatusContainer');
    if (data.trunks.length === 0) {
        container.innerHTML = '<div class="empty-state" style="grid-column: 1 / -1;"><i class="fa fa-plug"></i><p>No hay troncales monitoreadas configuradas</p></div>';
        return;
    }

    var html = '';
    var activeCount = 0;
    for (var i = 0; i < data.trunks.length; i++) {
        var t = data.trunks[i];
        if (t.status === 'OK') activeCount++;
        var monClass = t.enabled == 1 ? 'active' : 'paused';
        var monText = t.enabled == 1 ? 'Activo' : 'Pausado';
        html += '<div class="trunk-card" data-trunk-id="' + t.id + '">';
        html += '<div class="trunk-info"><h4>' + t.name + ' <span class="monitoring-tag ' + monClass + '">' + monText + '</span></h4>';
        html += '<span class="ip"><i class="fa fa-globe"></i> ' + t.ip + (t.port ? ':' + t.port : '') + '</span></div>';
        html += '<div class="trunk-status"><span class="status-badge ' + getStatusClass(t.status) + '">' + getStatusIcon(t.status) + ' ' + t.status + '</span>';
        if (t.latency) html += '<span class="latency">' + t.latency + '</span>';
        html += '</div></div>';
    }
    container.innerHTML = html;

    document.getElementById('totalTrunks').textContent = data.trunks.length;
    document.getElementById('activeTrunks').textContent = activeCount;
}

document.addEventListener('DOMContentLoaded', function() {
    refreshStatus();
    refreshInterval = setInterval(refreshStatus, 5000);
});
</script>

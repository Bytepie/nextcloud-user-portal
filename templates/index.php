<?php

declare(strict_types=1);

use OCP\Util;

Util::addScript(OCA\IamDiskbg\AppInfo\Application::APP_ID, 'main');


?>

<div id="iamdiskbg" style="min-width:100%;">

</div>

<style scoped lang="scss">
    h2 {
        font-weight: 600;
        font-size: 16px;
    }

    #MainContent {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 1rem;
    }

    .controls {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;

        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 1rem;

            .page-info {
                font-weight: bold;
                min-width: 100px;
                text-align: center;
            }
        }
    }

    .table-container {
        width: 100%;
        overflow-x: auto;
        background-color: var(--color-main-background);
        border-radius: var(--border-radius-large);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .table {
        width: 100%;
        border-collapse: collapse;

        th {
            text-align: left;
            padding: 12px 16px;
            background-color: var(--color-background-dark);
            border-bottom: 1px solid var(--color-border);
            font-weight: bold;
        }

        td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--color-border);
            vertical-align: middle;
        }
    }

    .user-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;

        &-icon {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        &--enabled &-icon {
            background-color: var(--color-success);
        }

        &--disabled &-icon {
            background-color: var(--color-error);
        }
    }

    .actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    @media (max-width: 768px) {
        .controls {
            flex-direction: column;
            align-items: stretch;

            .pagination-controls {
                justify-content: center;
            }
        }

        .table th,
        .table td {
            padding: 8px 12px;
        }

        .actions {
            flex-direction: column;
            gap: 4px;
        }
    }
</style>
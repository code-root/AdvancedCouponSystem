/**
 * DataTable Handler Module for Admin Panel
 * Server-side DataTables with custom filters, export buttons, and inline editing
 */

class DataTableHandler {
    constructor() {
        this.tables = new Map();
        this.init();
    }

    /**
     * Initialize DataTable handler
     */
    init() {
        this.bindTables();
    }

    /**
     * Bind all tables with data-datatable attribute
     */
    bindTables() {
        const datatableElements = document.querySelectorAll('table[data-datatable]');
        datatableElements.forEach(table => this.bindTable(table));
    }

    /**
     * Bind individual table
     */
    bindTable(table) {
        const tableId = table.id || `table_${Date.now()}`;
        const options = this.parseTableOptions(table);
        
        this.tables.set(tableId, {
            element: table,
            options: options,
            datatable: null
        });

        this.initializeDataTable(tableId);
    }

    /**
     * Parse table options from data attributes
     */
    parseTableOptions(table) {
        return {
            serverSide: table.dataset.serverSide === 'true',
            ajaxUrl: table.dataset.ajaxUrl,
            columns: this.parseColumns(table),
            pageLength: parseInt(table.dataset.pageLength) || 25,
            order: this.parseOrder(table.dataset.order),
            search: table.dataset.search !== 'false',
            paging: table.dataset.paging !== 'false',
            info: table.dataset.info !== 'false',
            responsive: table.dataset.responsive !== 'false',
            export: table.dataset.export === 'true',
            inlineEdit: table.dataset.inlineEdit === 'true',
            selectable: table.dataset.selectable === 'true',
            bulkActions: table.dataset.bulkActions === 'true',
            filters: this.parseFilters(table),
            language: {
                processing: "Loading...",
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        };
    }

    /**
     * Parse columns from table headers
     */
    parseColumns(table) {
        const headers = table.querySelectorAll('thead th');
        const columns = [];

        headers.forEach((header, index) => {
            const column = {
                data: header.dataset.data || index,
                title: header.textContent.trim(),
                orderable: header.dataset.orderable !== 'false',
                searchable: header.dataset.searchable !== 'false',
                visible: header.dataset.visible !== 'false',
                width: header.dataset.width,
                className: header.dataset.className,
                render: header.dataset.render ? window[header.dataset.render] : null
            };

            // Handle action columns
            if (header.dataset.actions) {
                column.orderable = false;
                column.searchable = false;
                column.render = (data, type, row) => this.renderActions(data, type, row, header.dataset.actions);
            }

            columns.push(column);
        });

        return columns;
    }

    /**
     * Parse order configuration
     */
    parseOrder(orderString) {
        if (!orderString) return [[0, 'asc']];
        
        try {
            return JSON.parse(orderString);
        } catch {
            return [[0, 'asc']];
        }
    }

    /**
     * Parse filters configuration
     */
    parseFilters(table) {
        const filtersContainer = table.parentNode.querySelector('.datatable-filters');
        if (!filtersContainer) return [];

        const filters = [];
        const filterElements = filtersContainer.querySelectorAll('[data-filter]');

        filterElements.forEach(filter => {
            filters.push({
                name: filter.dataset.filter,
                type: filter.dataset.filterType || 'text',
                element: filter,
                value: filter.value
            });
        });

        return filters;
    }

    /**
     * Initialize DataTable
     */
    initializeDataTable(tableId) {
        const tableData = this.tables.get(tableId);
        if (!tableData) return;

        const { element: table, options } = tableData;

        // Prepare DataTable configuration
        const config = {
            processing: true,
            serverSide: options.serverSide,
            ajax: options.serverSide ? {
                url: options.ajaxUrl,
                type: 'GET',
                data: (d) => this.prepareAjaxData(d, options)
            } : null,
            columns: options.columns,
            pageLength: options.pageLength,
            order: options.order,
            searching: options.search,
            paging: options.paging,
            info: options.info,
            responsive: options.responsive,
            dom: this.buildDom(options),
            language: options.language,
            initComplete: () => this.onInitComplete(tableId),
            drawCallback: () => this.onDrawCallback(tableId)
        };

        // Initialize DataTable
        const datatable = $(table).DataTable(config);
        tableData.datatable = datatable;

        // Setup filters
        if (options.filters.length > 0) {
            this.setupFilters(tableId);
        }

        // Setup bulk actions
        if (options.bulkActions) {
            this.setupBulkActions(tableId);
        }
    }

    /**
     * Build DOM configuration
     */
    buildDom(options) {
        let dom = '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>';
        dom += '<"row"<"col-sm-12"tr>>';
        dom += '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>';

        if (options.export) {
            dom = '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>';
            dom += '<"row"<"col-sm-12"B>>';
            dom += '<"row"<"col-sm-12"tr>>';
            dom += '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>';
        }

        return dom;
    }

    /**
     * Prepare AJAX data
     */
    prepareAjaxData(data, options) {
        // Add filter data
        if (options.filters) {
            options.filters.forEach(filter => {
                if (filter.element && filter.element.value) {
                    data[filter.name] = filter.element.value;
                }
            });
        }

        return data;
    }

    /**
     * Setup filters
     */
    setupFilters(tableId) {
        const tableData = this.tables.get(tableId);
        if (!tableData) return;

        const { options } = tableData;

        options.filters.forEach(filter => {
            if (filter.element) {
                filter.element.addEventListener('change', () => {
                    this.reloadTable(tableId);
                });

                filter.element.addEventListener('keyup', (e) => {
                    if (e.key === 'Enter') {
                        this.reloadTable(tableId);
                    }
                });
            }
        });
    }

    /**
     * Setup bulk actions
     */
    setupBulkActions(tableId) {
        const tableData = this.tables.get(tableId);
        if (!tableData) return;

        const { element: table, datatable } = tableData;

        // Add select all checkbox
        const thead = table.querySelector('thead tr');
        const selectAllTh = document.createElement('th');
        selectAllTh.innerHTML = '<input type="checkbox" id="select-all">';
        thead.insertBefore(selectAllTh, thead.firstChild);

        // Handle select all
        const selectAllCheckbox = selectAllTh.querySelector('#select-all');
        selectAllCheckbox.addEventListener('change', (e) => {
            const checkboxes = table.querySelectorAll('tbody input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = e.target.checked;
            });
            this.updateBulkActions(tableId);
        });

        // Add bulk actions container
        const bulkActionsContainer = document.createElement('div');
        bulkActionsContainer.className = 'bulk-actions mb-3';
        bulkActionsContainer.style.display = 'none';
        bulkActionsContainer.innerHTML = `
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete">
                    <i class="ti ti-trash"></i> Delete Selected
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning" data-action="export">
                    <i class="ti ti-download"></i> Export Selected
                </button>
            </div>
        `;

        table.parentNode.insertBefore(bulkActionsContainer, table);

        // Handle bulk actions
        bulkActionsContainer.addEventListener('click', (e) => {
            const action = e.target.closest('[data-action]')?.dataset.action;
            if (action) {
                this.handleBulkAction(tableId, action);
            }
        });
    }

    /**
     * Update bulk actions visibility
     */
    updateBulkActions(tableId) {
        const tableData = this.tables.get(tableId);
        if (!tableData) return;

        const { element: table } = tableData;
        const selectedCheckboxes = table.querySelectorAll('tbody input[type="checkbox"]:checked');
        const bulkActionsContainer = table.parentNode.querySelector('.bulk-actions');

        if (bulkActionsContainer) {
            bulkActionsContainer.style.display = selectedCheckboxes.length > 0 ? 'block' : 'none';
        }
    }

    /**
     * Handle bulk action
     */
    async handleBulkAction(tableId, action) {
        const tableData = this.tables.get(tableId);
        if (!tableData) return;

        const { element: table } = tableData;
        const selectedCheckboxes = table.querySelectorAll('tbody input[type="checkbox"]:checked');
        const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);

        if (selectedIds.length === 0) {
            ajaxHelper.showNotification('Please select items to perform this action', 'warning');
            return;
        }

        try {
            const response = await ajaxHelper.post(`/admin/bulk/${action}`, {
                ids: selectedIds
            });

            ajaxHelper.showNotification(response.message, 'success');
            this.reloadTable(tableId);

        } catch (error) {
            ajaxHelper.showNotification(error.message, 'error');
        }
    }

    /**
     * Render actions column
     */
    renderActions(data, type, row, actionsConfig) {
        if (type !== 'display') return '';

        const actions = actionsConfig.split(',');
        let html = '<div class="btn-group btn-group-sm">';

        actions.forEach(action => {
            const actionData = action.trim().split(':');
            const actionName = actionData[0];
            const actionUrl = actionData[1] || '#';

            switch (actionName) {
                case 'edit':
                    html += `<a href="${actionUrl.replace(':id', row.id)}" class="btn btn-outline-primary" title="Edit">
                        <i class="ti ti-edit"></i>
                    </a>`;
                    break;
                case 'delete':
                    html += `<button type="button" class="btn btn-outline-danger" onclick="deleteItem(${row.id})" title="Delete">
                        <i class="ti ti-trash"></i>
                    </button>`;
                    break;
                case 'view':
                    html += `<a href="${actionUrl.replace(':id', row.id)}" class="btn btn-outline-info" title="View">
                        <i class="ti ti-eye"></i>
                    </a>`;
                    break;
            }
        });

        html += '</div>';
        return html;
    }

    /**
     * Reload table
     */
    reloadTable(tableId) {
        const tableData = this.tables.get(tableId);
        if (tableData && tableData.datatable) {
            tableData.datatable.ajax.reload();
        }
    }

    /**
     * On init complete callback
     */
    onInitComplete(tableId) {
        const tableData = this.tables.get(tableId);
        if (!tableData) return;

        const { element: table, options } = tableData;

        // Setup row selection
        if (options.selectable) {
            this.setupRowSelection(tableId);
        }

        // Setup inline editing
        if (options.inlineEdit) {
            this.setupInlineEditing(tableId);
        }

        // Trigger custom event
        table.dispatchEvent(new CustomEvent('datatable:init', {
            detail: { tableId, tableData }
        }));
    }

    /**
     * On draw callback
     */
    onDrawCallback(tableId) {
        const tableData = this.tables.get(tableId);
        if (!tableData) return;

        const { element: table } = tableData;

        // Update bulk actions
        this.updateBulkActions(tableId);

        // Trigger custom event
        table.dispatchEvent(new CustomEvent('datatable:draw', {
            detail: { tableId, tableData }
        }));
    }

    /**
     * Setup row selection
     */
    setupRowSelection(tableId) {
        const tableData = this.tables.get(tableId);
        if (!tableData) return;

        const { element: table } = tableData;

        // Add checkbox to each row
        const tbody = table.querySelector('tbody');
        if (tbody) {
            tbody.addEventListener('change', (e) => {
                if (e.target.type === 'checkbox') {
                    this.updateBulkActions(tableId);
                }
            });
        }
    }

    /**
     * Setup inline editing
     */
    setupInlineEditing(tableId) {
        const tableData = this.tables.get(tableId);
        if (!tableData) return;

        const { element: table } = tableData;

        // Add edit buttons and inline editing functionality
        // This would be implemented based on specific requirements
    }

    /**
     * Get table by ID
     */
    getTable(tableId) {
        return this.tables.get(tableId);
    }

    /**
     * Destroy table
     */
    destroyTable(tableId) {
        const tableData = this.tables.get(tableId);
        if (tableData && tableData.datatable) {
            tableData.datatable.destroy();
            this.tables.delete(tableId);
        }
    }
}

// Create global instance
window.dataTableHandler = new DataTableHandler();

// Export for module usage
export default DataTableHandler;


-- Drop existing tables if they exist
DROP TABLE IF EXISTS public.expense_approvals CASCADE;
DROP TABLE IF EXISTS public.budgets CASCADE;
DROP TABLE IF EXISTS public.budget_allocations CASCADE;
DROP TABLE IF EXISTS public.cost_centers CASCADE;
DROP TABLE IF EXISTS public.cost_center_allocations CASCADE;
DROP TABLE IF EXISTS public.financial_reports CASCADE;
DROP TABLE IF EXISTS public.financial_report_items CASCADE;

-- Financial Management Tables

-- Budgets table
CREATE TABLE public.budgets (
    id SERIAL PRIMARY KEY,
    department_id INTEGER REFERENCES departments(id),
    fiscal_year INTEGER NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    category VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_by INTEGER REFERENCES users(id),
    updated_by INTEGER REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Budget allocations table
CREATE TABLE public.budget_allocations (
    id SERIAL PRIMARY KEY,
    budget_id INTEGER REFERENCES budgets(id),
    vehicle_id INTEGER REFERENCES vehicles(id),
    amount DECIMAL(15,2) NOT NULL,
    allocation_type VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_by INTEGER REFERENCES users(id),
    updated_by INTEGER REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Expense approvals table
CREATE TABLE public.expense_approvals (
    id SERIAL PRIMARY KEY,
    expense_id INTEGER REFERENCES trip_expenses(id),
    approver_id INTEGER REFERENCES users(id),
    status VARCHAR(20) NOT NULL,
    comments TEXT,
    approval_date TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cost centers table
CREATE TABLE public.cost_centers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    department_id INTEGER REFERENCES departments(id),
    status VARCHAR(20) DEFAULT 'active',
    created_by INTEGER REFERENCES users(id),
    updated_by INTEGER REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cost center allocations
CREATE TABLE public.cost_center_allocations (
    id SERIAL PRIMARY KEY,
    cost_center_id INTEGER REFERENCES cost_centers(id),
    vehicle_id INTEGER REFERENCES vehicles(id),
    allocation_percentage DECIMAL(5,2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_by INTEGER REFERENCES users(id),
    updated_by INTEGER REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Financial reports table
CREATE TABLE public.financial_reports (
    id SERIAL PRIMARY KEY,
    report_type VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    department_id INTEGER REFERENCES departments(id),
    generated_by INTEGER REFERENCES users(id),
    status VARCHAR(20) DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Financial report items
CREATE TABLE public.financial_report_items (
    id SERIAL PRIMARY KEY,
    report_id INTEGER REFERENCES financial_reports(id),
    category VARCHAR(50) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes
CREATE INDEX idx_budgets_department ON public.budgets USING btree (department_id);
CREATE INDEX idx_budgets_fiscal_year ON public.budgets USING btree (fiscal_year);
CREATE INDEX idx_budget_allocations_budget ON public.budget_allocations USING btree (budget_id);
CREATE INDEX idx_budget_allocations_vehicle ON public.budget_allocations USING btree (vehicle_id);
CREATE INDEX idx_expense_approvals_expense ON public.expense_approvals USING btree (expense_id);
CREATE INDEX idx_expense_approvals_approver ON public.expense_approvals USING btree (approver_id);
CREATE INDEX idx_cost_centers_department ON public.cost_centers USING btree (department_id);
CREATE INDEX idx_cost_center_allocations_center ON public.cost_center_allocations USING btree (cost_center_id);
CREATE INDEX idx_cost_center_allocations_vehicle ON public.cost_center_allocations USING btree (vehicle_id);
CREATE INDEX idx_financial_reports_department ON public.financial_reports USING btree (department_id);
CREATE INDEX idx_financial_report_items_report ON public.financial_report_items USING btree (report_id);

-- Add triggers for updated_at
CREATE TRIGGER update_budgets_updated_at
    BEFORE UPDATE ON public.budgets
    FOR EACH ROW
    EXECUTE FUNCTION public.update_updated_at_column();

CREATE TRIGGER update_budget_allocations_updated_at
    BEFORE UPDATE ON public.budget_allocations
    FOR EACH ROW
    EXECUTE FUNCTION public.update_updated_at_column();

CREATE TRIGGER update_expense_approvals_updated_at
    BEFORE UPDATE ON public.expense_approvals
    FOR EACH ROW
    EXECUTE FUNCTION public.update_updated_at_column();

CREATE TRIGGER update_cost_centers_updated_at
    BEFORE UPDATE ON public.cost_centers
    FOR EACH ROW
    EXECUTE FUNCTION public.update_updated_at_column();

CREATE TRIGGER update_cost_center_allocations_updated_at
    BEFORE UPDATE ON public.cost_center_allocations
    FOR EACH ROW
    EXECUTE FUNCTION public.update_updated_at_column();

CREATE TRIGGER update_financial_reports_updated_at
    BEFORE UPDATE ON public.financial_reports
    FOR EACH ROW
    EXECUTE FUNCTION public.update_updated_at_column();

CREATE TRIGGER update_financial_report_items_updated_at
    BEFORE UPDATE ON public.financial_report_items
    FOR EACH ROW
    EXECUTE FUNCTION public.update_updated_at_column(); 
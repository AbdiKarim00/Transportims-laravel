-- Create service_providers table
CREATE TABLE IF NOT EXISTS public.service_providers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) UNIQUE,
    type VARCHAR(100),
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(255),
    contact_person VARCHAR(255),
    status BOOLEAN DEFAULT true,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add service_provider_id column to maintenance_schedules table if it doesn't exist
ALTER TABLE public.maintenance_schedules
ADD COLUMN IF NOT EXISTS service_provider_id BIGINT REFERENCES public.service_providers(id);

-- Add some sample data for testing
INSERT INTO public.service_providers (name, code, type, address, phone, email, contact_person, status) VALUES
('AutoTech Services', 'ATS001', 'Maintenance', '123 Industrial Ave', '+1234567890', 'info@autotech.com', 'John Smith', true),
('Fleet Solutions Ltd', 'FSL002', 'Parts Supply', '456 Business Park', '+1234567891', 'contact@fleetsolutions.com', 'Jane Doe', true),
('Express Repairs', 'ER003', 'Emergency Repair', '789 Service Street', '+1234567892', 'support@expressrepairs.com', 'Mike Johnson', true);

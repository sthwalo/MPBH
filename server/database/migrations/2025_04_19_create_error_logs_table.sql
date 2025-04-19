-- Create error logs table
CREATE TABLE IF NOT EXISTS error_logs (
    id SERIAL PRIMARY KEY,
    message TEXT NOT NULL,
    context JSONB,
    exception_class VARCHAR(255),
    exception_message TEXT,
    exception_trace TEXT,
    user_id INTEGER,
    request_path VARCHAR(255),
    request_method VARCHAR(10),
    http_status INTEGER,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Add foreign key constraint after users table exists
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'users') THEN
        ALTER TABLE error_logs ADD CONSTRAINT fk_error_logs_user_id FOREIGN KEY (user_id) REFERENCES users(id);
    END IF;
END $$;

-- Create indexes for faster querying
CREATE INDEX idx_error_logs_created_at ON error_logs(created_at);
CREATE INDEX idx_error_logs_user_id ON error_logs(user_id);
CREATE INDEX idx_error_logs_http_status ON error_logs(http_status);
CREATE INDEX idx_error_logs_request_path ON error_logs(request_path);

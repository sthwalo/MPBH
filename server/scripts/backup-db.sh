#!/bin/bash

# Load environment variables
if [ -f "../.env" ]; then
  source ../.env
else
  echo "Error: .env file not found"
  exit 1
fi

# Set variables
BACKUP_DIR="../backups"
DATETIME=$(date +"%Y-%m-%d_%H-%M-%S")
PG_BACKUP_FILE="${BACKUP_DIR}/mpbh_pg_backup_${DATETIME}.sql"
LOGFILE="${BACKUP_DIR}/backup_log.txt"
S3_BUCKET="${S3_BUCKET:-mpbh-backups}"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Log function
log_message() {
  echo "[$(date +"%Y-%m-%d %H:%M:%S")] $1" | tee -a "$LOGFILE"
}

# Start backup process
log_message "Starting PostgreSQL database backup"

# Create PostgreSQL dump
log_message "Creating PostgreSQL dump..."
PGPASSWORD="${DB_PASSWORD}" pg_dump -h "${DB_HOST}" -U "${DB_USER}" "${DB_NAME}" > "$PG_BACKUP_FILE"

if [ $? -eq 0 ]; then
  log_message "PostgreSQL dump created successfully: $PG_BACKUP_FILE"
  
  # Compress the backup file
  log_message "Compressing backup file..."
  gzip -f "$PG_BACKUP_FILE"
  COMPRESSED_FILE="${PG_BACKUP_FILE}.gz"
  
  # Upload to S3 if AWS CLI is available
  if command -v aws >/dev/null 2>&1 && [ -n "${AWS_ACCESS_KEY_ID}" ] && [ -n "${AWS_SECRET_ACCESS_KEY}" ]; then
    log_message "Uploading to S3 bucket: $S3_BUCKET"
    aws s3 cp "$COMPRESSED_FILE" "s3://${S3_BUCKET}/$(basename "$COMPRESSED_FILE")"
    
    if [ $? -eq 0 ]; then
      log_message "Upload to S3 successful"
    else
      log_message "Error: Failed to upload to S3"
    fi
  else
    log_message "AWS CLI not available or credentials not set. Skipping S3 upload."
  fi
  
  # Clean up old backups (keep last 7 days)
  log_message "Cleaning up old backups..."
  find "$BACKUP_DIR" -name "mpbh_pg_backup_*.sql.gz" -type f -mtime +7 -delete
  
  log_message "Backup process completed successfully"
  
  # Print backup summary
  BACKUP_SIZE=$(du -h "$COMPRESSED_FILE" | cut -f1)
  echo -e "\nBackup Summary:"
  echo "  File: $(basename "$COMPRESSED_FILE")"
  echo "  Size: $BACKUP_SIZE"
  echo "  Location: $BACKUP_DIR"
  if command -v aws >/dev/null 2>&1 && [ -n "${AWS_ACCESS_KEY_ID}" ] && [ -n "${AWS_SECRET_ACCESS_KEY}" ]; then
    echo "  S3 Bucket: $S3_BUCKET"
  fi
else
  log_message "Error: PostgreSQL dump failed"
  exit 1
fi

exit 0
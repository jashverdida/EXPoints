# Database Backups

This directory stores database backups created by the backup script.

## Creating a Backup

```bash
php database/backup.php
```

Backups are automatically named with timestamps: `expoints_backup_YYYY-MM-DD_HHMMSS.sql`

## Restoring a Backup

```bash
# Windows (PowerShell)
Get-Content database\backups\expoints_backup_2024-01-15_143022.sql | mysql -u root -p expoints_db

# Mac/Linux
mysql -u root -p expoints_db < database/backups/expoints_backup_2024-01-15_143022.sql
```

## Best Practices

- Create a backup before running migrations or major updates
- Keep at least 5 recent backups
- Backups are in `.gitignore` and won't be committed
- Consider backing up before pulling major changes

## Cleanup

To delete old backups:

```bash
# Windows
Remove-Item database\backups\expoints_backup_*.sql -Exclude *$(Get-Date -Format 'yyyy-MM-dd')* 

# Mac/Linux  
find database/backups -name "expoints_backup_*.sql" -mtime +7 -delete
```

# Data Sync System Guide

## Overview

The Data Sync System is a comprehensive solution for automatically syncing data from affiliate networks (like Boostiny, Admitad, etc.) on a scheduled basis using Laravel Queue and Scheduler.

## Features

- **Automated Scheduling**: Create multiple sync schedules with custom intervals
- **Queue-based Processing**: All sync jobs run in the background via Laravel Queue
- **Flexible Date Ranges**: Choose from predefined or custom date ranges
- **Network Selection**: Sync from one or multiple networks simultaneously
- **Sync Type Control**: Choose to sync all data or specific types (campaigns, coupons, purchases)
- **Rate Limiting**: Set maximum runs per day to avoid API rate limits
- **Comprehensive Logging**: Full history of all sync operations with detailed metadata
- **Manual Sync**: Trigger immediate syncs outside of schedules
- **Status Management**: Enable/disable schedules with a single click

## System Architecture

### Database Tables

1. **sync_schedules**: Stores all sync schedule configurations
2. **sync_logs**: Records every sync operation with detailed results

### Key Components

1. **Models**:
   - `SyncSchedule`: Manages schedule configurations
   - `SyncLog`: Records sync operation history

2. **Job**:
   - `ProcessNetworkSync`: Queued job that performs the actual data syncing

3. **Commands**:
   - `sync:process-scheduled`: Checks for due schedules and dispatches jobs
   - `sync:reset-daily-counters`: Resets daily run counters at midnight

4. **Controller**:
   - `SyncController`: Handles all web interface operations

5. **Service**:
   - `DataSyncService`: Business logic for syncing operations

## Setup Instructions

### 1. Database Migration

The migrations have already been run:
```bash
php artisan migrate
```

### 2. Configure Queue Driver

In your `.env` file, set your preferred queue driver:
```env
QUEUE_CONNECTION=database
# or
QUEUE_CONNECTION=redis
```

### 3. Set Up Cron Job

Add this to your server's crontab to run the Laravel Scheduler:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Start Queue Worker

For development:
```bash
php artisan queue:work
```

For production (using Supervisor), create `/etc/supervisor/conf.d/laravel-worker.conf`:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-your-project/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path-to-your-project/storage/logs/worker.log
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## Usage Guide

### Creating a Sync Schedule

1. Navigate to **Data Sync > Schedules**
2. Click **Create Schedule**
3. Fill in the form:
   - **Schedule Name**: Give it a meaningful name (e.g., "Daily Morning Sync")
   - **Select Networks**: Choose one or more networks to sync from
   - **Sync Type**: Choose what data to sync (All, Campaigns, Coupons, or Purchases)
   - **Interval**: Set how often to run (every 10 minutes, hourly, daily, etc.)
   - **Max Runs Per Day**: Limit how many times it can run in a day
   - **Date Range**: Choose what date range to fetch data for
   - **Status**: Toggle to activate immediately
4. Click **Create Schedule**

### Managing Schedules

From the Schedules list page, you can:

- **Toggle Status**: Enable or disable a schedule
- **Run Now**: Trigger an immediate sync
- **Edit**: Modify schedule settings
- **Delete**: Remove a schedule permanently

### Manual Sync

To perform a one-time sync without creating a schedule:

1. Navigate to **Data Sync > Logs**
2. (Feature to be added in UI, but API endpoint `/sync/manual` is ready)

### Viewing Sync Logs

1. Navigate to **Data Sync > Sync Logs**
2. Use filters to find specific logs:
   - Filter by Network
   - Filter by Status (Pending, Processing, Completed, Failed)
   - Filter by Schedule
   - Filter by Date Range
3. Click **View** on any log to see detailed information

### Monitoring System

Check the **Data Sync > Settings** page for:
- Queue configuration
- Scheduler status
- Queue worker status
- System information

## API Endpoints

### Schedules
- `GET /sync/schedules` - List all schedules
- `GET /sync/schedules/create` - Show create form
- `POST /sync/schedules` - Create new schedule
- `GET /sync/schedules/{id}/edit` - Show edit form
- `PUT /sync/schedules/{id}` - Update schedule
- `DELETE /sync/schedules/{id}` - Delete schedule
- `POST /sync/schedules/{id}/toggle` - Toggle active status
- `POST /sync/schedules/{id}/run` - Run schedule immediately

### Logs
- `GET /sync/logs` - List all logs (with filters)
- `GET /sync/logs/{id}` - View log details

### Manual Sync
- `POST /sync/manual` - Trigger manual sync

### Settings
- `GET /sync/settings` - View settings page

## Schedule Intervals

Available preset intervals:
- Every 10 Minutes
- Every 30 Minutes
- Every Hour
- Every 2 Hours
- Every 6 Hours
- Every 12 Hours
- Daily (24 Hours)

## Date Range Types

- **Today**: Syncs today's data only
- **Yesterday**: Syncs yesterday's data
- **Last 7 Days**: Syncs data from the last week
- **Last 30 Days**: Syncs data from the last month
- **Current Month**: Syncs from start of current month to today
- **Previous Month**: Syncs the complete previous month (1st to last day)
- **Custom**: Choose your own from/to dates

## Quick Sync Feature

The **Quick Sync** page provides an instant way to fetch data without creating a schedule:

### When to Use Quick Sync

- One-time data pulls
- Testing network connections
- Fetching historical data for a specific period
- Urgent data synchronization needs
- Syncing data for multiple networks at once

### How to Use Quick Sync

1. Navigate to **Data Sync > Quick Sync**
2. Select one or more networks from the dropdown
3. Choose the data type to sync (All, Campaigns, Coupons, or Purchases)
4. Select a date range:
   - Use preset options (Today, Yesterday, Current Month, etc.)
   - Or select a custom date range
5. Click **Start Sync Now**
6. Monitor the progress in real-time
7. View results when completed

### Quick Presets

The Quick Sync page includes convenient preset buttons:
- **Sync Today's Data**: Instantly fetch today's data from all selected networks
- **Sync Yesterday's Data**: Get yesterday's complete data
- **Sync Current Month**: Fetch data from the 1st of the month to today
- **Sync Previous Month**: Get the complete previous month's data

### Features

- **Real-time Progress**: Track sync progress as it happens
- **Multi-Network Support**: Sync from multiple networks simultaneously
- **Flexible Date Ranges**: Choose any date range you need
- **Background Processing**: Syncs run in the queue without blocking the UI
- **Automatic Logging**: All quick syncs are recorded in Sync Logs
- **Recent History**: View your 5 most recent quick syncs

## Troubleshooting

### Schedules Not Running

1. Check if cron job is set up correctly:
   ```bash
   crontab -l
   ```

2. Test scheduler manually:
   ```bash
   php artisan schedule:run
   ```

3. Check scheduler output:
   ```bash
   php artisan schedule:list
   ```

### Jobs Not Processing

1. Check if queue worker is running:
   ```bash
   ps aux | grep "queue:work"
   ```

2. Check failed jobs:
   ```bash
   php artisan queue:failed
   ```

3. Retry failed jobs:
   ```bash
   php artisan queue:retry all
   ```

### Sync Errors

1. Check sync logs in the UI for error messages
2. Check Laravel logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. Test network connection manually:
   - Navigate to **Networks** and test connection

## Best Practices

1. **Start Small**: Begin with longer intervals (hourly or daily) and adjust based on needs
2. **Monitor API Limits**: Each network has API rate limits; set appropriate intervals
3. **Use Max Runs Per Day**: Protect against unexpected behavior by limiting daily runs
4. **Regular Monitoring**: Check sync logs regularly for failures
5. **Queue Management**: Monitor queue size and worker performance
6. **Date Range Selection**: Use appropriate date ranges to avoid fetching duplicate data

## Performance Considerations

- Each sync schedule can sync multiple networks, but each network is processed as a separate job
- Jobs are queued and processed sequentially by available workers
- Heavy syncing (long date ranges, multiple networks) may take time
- Consider increasing queue workers for high-volume syncing

## Security

- All sync operations are scoped to the authenticated user
- Network credentials are encrypted in the database
- API keys are never exposed in logs or UI
- Rate limiting is applied to all sync endpoints

## Future Enhancements

Potential features for future development:
- Email notifications on sync failures
- Webhook notifications
- Sync conflict resolution
- Data deduplication strategies
- Real-time sync status dashboard
- Advanced scheduling (specific times, days of week)
- Sync performance analytics
- Export sync logs to CSV/Excel

## Support

For issues or questions:
1. Check logs first (UI and Laravel logs)
2. Verify queue and scheduler are running
3. Test network connections individually
4. Review this guide for common issues


-- Add winCon column to gcpTradeData table
-- Run this SQL if you already have gcpTradeData table without winCon field

USE dynamic_chart;

-- Check if winCon column exists, if not add it
ALTER TABLE gcpTradeData 
ADD COLUMN IF NOT EXISTS winCon INT DEFAULT NULL AFTER WinStatus;

-- Verify the column was added
SHOW COLUMNS FROM gcpTradeData LIKE 'winCon';

-- Optional: Update winCon based on existing data
-- (if you want to calculate winCon from existing trades)
UPDATE gcpTradeData 
SET winCon = 0 
WHERE winCon IS NULL AND WinStatus = 'win';

UPDATE gcpTradeData 
SET winCon = 1 
WHERE winCon IS NULL AND WinStatus = 'win' AND lossCon = 0;

SELECT 'winCon column added successfully!' as status;

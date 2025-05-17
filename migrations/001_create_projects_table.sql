CREATE TABLE `projects` (
  `id` int NOT NULL,
  `title` varchar(500) NOT NULL,
  `start_date` date NOT NULL,
  `segment_length` int NOT NULL DEFAULT '7',
  `total_segments` int NOT NULL DEFAULT '12',
  `minimum_percentage` int NOT NULL DEFAULT '70',
  `decrease_percentage` int NOT NULL DEFAULT '10',
  `finished` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

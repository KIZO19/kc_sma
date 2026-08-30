ALTER TABLE agents
  MODIFY ecole_id INT NULL;

-- This allows a pending agent to be created before school assignment.
-- The admin school validates and assigns the school later before activation.

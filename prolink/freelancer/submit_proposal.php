<form method="POST" action="submit_proposal.php">
    Bid: <input type="number" name="bid"><br>
    Cover Letter: <textarea name="cover"></textarea>
    <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
    <button type="submit">Apply</button>
</form>
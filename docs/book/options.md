# options

## Enforce state for matches

You can enforce a certain state for the matches, by adding
the follow option the the `settings` table 

Replace `available` with the `tag_value` of your desired state

```sql
INSERT INTO `settings` (`settings_key`, `settings_value`) VALUES 
('bookrequest-enforce-state', 'available'); 
```

Generic enforcements are planned, so you can enforce any tag
by option.
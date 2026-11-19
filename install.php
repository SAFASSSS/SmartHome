<?php // Run ONCE in the browser after importing schema.sql, then DELETE this file.
require_once 'includes/bootstrap.php';
if ((int)q('SELECT COUNT(*) FROM users')->fetchColumn() > 0) exit('Already installed. Delete install.php.');
$pw = password_hash('password123', PASSWORD_DEFAULT);
$b = [['Block A', '12 Greenfield Avenue', 8], ['Block B', '14 Greenfield Avenue', 10], ['Block C', '16 Greenfield Avenue', 6]];
foreach ($b as $x) q('INSERT INTO buildings (name,address,floors) VALUES (?,?,?)', $x);
$mk = function ($n, $e, $r, $bid = null, $unit = null, $sp = null) use ($pw) { q("INSERT INTO users (name,email,phone,password_hash,role,status,building_id,unit,specialty) VALUES (?,?,?,?,?, 'active',?,?,?)", [$n, $e, '01700000000', $pw, $r, $bid, $unit, $sp]); return (int)db()->lastInsertId(); };
$admin = $mk('Sarah Admin', 'admin@smartnest.test', 'admin');
$m1 = $mk('Kevin Hart', 'manager@smartnest.test', 'manager', 1); $m2 = $mk('Lisa Park', 'manager2@smartnest.test', 'manager', 2);
q('UPDATE buildings SET manager_id=? WHERE id=1', [$m1]); q('UPDATE buildings SET manager_id=? WHERE id=2', [$m2]);
$t1 = $mk('Marcus Thompson', 'tech@smartnest.test', 'technician', null, null, 'Plumbing'); $t2 = $mk('Dana Ruiz', 'tech2@smartnest.test', 'technician', null, null, 'Electrical');
$r1 = $mk('Emma Wilson', 'resident@smartnest.test', 'resident', 1, '4B'); $r2 = $mk('Noah Carter', 'resident2@smartnest.test', 'resident', 2, '2A');
$extra = [$mk('Olivia Brown', 'resident3@smartnest.test', 'resident', 1, '5A'), $mk('Liam Davis', 'resident4@smartnest.test', 'resident', 2, '3C'), $mk('Ava Miller', 'resident5@smartnest.test', 'resident', 3, '1B'), $mk('Ethan Clark', 'resident6@smartnest.test', 'resident', 3, '2D')];
$allRes = array_merge([$r1, $r2], $extra);
q("INSERT INTO users (name,email,password_hash,role,status,building_id,unit) VALUES ('Pending Person','pending@smartnest.test',?, 'resident','pending',1,'7C')", [$pw]);
$u1600 = fn($id) => "https://images.unsplash.com/photo-$id?w=1200&q=75&auto=format&fit=crop";
$flats = [
 [1,'3A',2,950,'rent',2400,'Parking, Gym, Security', $u1600('1665249934445-1de680641f50')],
 [2,'6C',3,1200,'rent',3100,'Parking, Elevator, Pool', $u1600('1738168279272-c08d6dd22002')],
 [3,'2B',1,620,'rent',1500,'Security, Rooftop', $u1600('1589834390005-5d4fb9bf3d32')],
 [1,'8D',3,1400,'sale',385000,'Parking, Gym, Elevator', $u1600('1738168246881-40f35f8aba0a')],
 [2,'4A',2,880,'sale',240000,'Elevator, Generator', $u1600('1606074280798-2dabb75ce10c')],
];
foreach ($flats as $f) q('INSERT INTO flats (building_id,unit,bedrooms,area_sqft,listing_type,price,amenities,photo) VALUES (?,?,?,?,?,?,?,?)', $f);
$c = [[$r1,'Plumbing','Severe water leak under kitchen sink flooding the cabinet.','urgent','assigned',$t1],[$r1,'Electrical','Main circuit breaker tripping repeatedly, no power in bedroom.','urgent','in_progress',$t2],[$r1,'HVAC','Air conditioning unit making loud grinding noise.','normal','pending',null],[$r2,'Cleaning','Corridor lights flickering on floor 2.','normal','resolved',$t2]];
foreach ($c as $x) q('INSERT INTO complaints (resident_id,category,description,priority,status,technician_id) VALUES (?,?,?,?,?,?)', $x);
// 6 months of complaint history so the charts have shape
$cats = ['Plumbing','Electrical','HVAC','Security','Cleaning','Other']; $pr = ['low','normal','normal','urgent'];
for ($i = 6; $i >= 1; $i--) { $n = rand(6, 14) + (6 - $i);
  for ($k = 0; $k < $n; $k++) { $day = date('Y-m-d H:i:s', strtotime("-$i month +" . rand(0, 24) . ' day'));
    $done = rand(1, 10) > 2;
    q('INSERT INTO complaints (resident_id,category,description,priority,status,technician_id,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?)',
      [$allRes[array_rand($allRes)], $cats[array_rand($cats)], 'Routine maintenance request.', $pr[array_rand($pr)], $done ? 'resolved' : 'pending', $done ? [$t1, $t2][rand(0, 1)] : null, $day, $done ? date('Y-m-d H:i:s', strtotime($day . ' +' . rand(1, 5) . ' day')) : $day]); } }
for ($i = 5; $i >= 0; $i--) { $mo = date('Y-m', strtotime("-$i month")); foreach ($allRes as $r) { $tot = 4300 + rand(0, 700); $paid = ($i || rand(0, 3) > 0); q('INSERT INTO bills (resident_id,month,electricity,water,gas,service,maintenance,other,total,status,paid_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)', [$r, $mo, 2520, 126, 216, 1500, 200, 50, $tot, $paid ? 'paid' : 'pending', $paid ? date('Y-m-d H:i:s', strtotime("-$i month")) : null]); } }
$n = [['Scheduled Water Supply Maintenance','Water will be interrupted on the 22nd from 9 AM to 3 PM for pipeline maintenance in Block A and B. Please store water in advance.','Maintenance'],['Community Barbecue Event','Join us for a community barbecue this weekend at the rooftop terrace from 5 PM. RSVP to the management office.','Event'],['Parking Lot Re-marking','The parking lot will be re-marked on the 28th. Please remove vehicles from basement parking by 6 AM.','Notice'],['Emergency: Gas Leak Detected Block C','Emergency gas leak detected in Block C corridor 2. Evacuate immediately and call emergency services. Do not use elevators.','Emergency']];
foreach ($n as $x) q('INSERT INTO notices (title,body,type,created_by) VALUES (?,?,?,?)', [$x[0], $x[1], $x[2], $admin]);
foreach ([$r1, $r2] as $r) notify($r, 'Welcome to SmartNest!');
q('INSERT INTO messages (sender_id,receiver_id,body) VALUES (?,?,?),(?,?,?)', [$m1, $r1, 'Your complaint TK-0001 has been assigned to Marcus Thompson.', $r1, $m1, 'Thank you! When should I expect him?']);
echo '<h2>SmartNest installed ✅</h2><p>All demo accounts use password <b>password123</b>:</p><ul><li>admin@smartnest.test</li><li>manager@smartnest.test</li><li>tech@smartnest.test</li><li>resident@smartnest.test</li></ul><p><b>Now delete install.php.</b> <a href="index.php">Open site →</a></p>';

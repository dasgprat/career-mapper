<html>
 <head>
  <title>PHP Test</title>
 </head>
 <body>


 <?php
/* Attempt MySQL server connection */
$link = mysqli_connect("classmysql.engr.oregonstate.edu", "cs540_hitchcob", "7363", "cs540_hitchcob");
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
 
// Attempt select query execution : Overall Jobs by city (2nd query in file)
$sql = "SELECT j.jid as alias_jid,j.j_title as alias_jtitle,p.p_name as alias_pname, c.cid as alias_cid, c.c_name as alias_cname, l.l_city as alias_l_city,l.l_state as alias_l_state from cm_job j left join cm_company c ON j.j_company = c.cid 
    left join cm_profession p ON j.j_profession = p.pid
    left join cm_location l ON j.j_location = l.lid
    order by l.l_city,l.l_state";

if($result = mysqli_query($link, $sql)){
    if(mysqli_num_rows($result) > 0){
        echo "<table border='1' bgcolor='#ECF0F1'>";
            echo "<tr>";
                echo "<th>jid</th>";
                echo "<th>jtitle</th>";
                echo "<th>pname</th>";
                echo "<th>cid</th>";
                echo "<th>cname</th>";
                echo "<th>lcity</th>";
                echo "<th>lstate</th>";
                
            echo "</tr>";
        while($row = mysqli_fetch_array($result)){
            echo "<tr>";
                echo "<td>" . $row['alias_jid'] . "</td>";
                echo "<td>" . $row['alias_jtitle'] . "</td>";
                echo "<td>" . $row['alias_pname'] . "</td>";
                echo "<td>" . $row['alias_cid'] . "</td>";
                echo "<td>" . $row['alias_cname'] . "</td>";
                echo "<td>" . $row['alias_l_city'] . "</td>";
                echo "<td>" . $row['alias_l_city'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        // Free result set
        mysqli_free_result($result);
    } else{
        echo "No records matching your query were found.";
    }
} else{
    echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
}
 
// Close connection
mysqli_close($link);
?>
 </body>
</html>
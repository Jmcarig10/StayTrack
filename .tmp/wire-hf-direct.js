const get=async(id)=>await figma.getNodeByIdAsync(id);
const link=async(fromId,toId)=>{const f=await get(fromId),t=await get(toId);if(!f||!t)return false;await f.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:t.id,navigation:'NAVIGATE',transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_AND_OUT'}}}]}]);return true;};
const login='278:3890', dash='283:1899', apt='282:1262', lease='282:1371', bills='282:1486', pays='282:1596', maint='278:3503', profile='278:3761', adminDash='277:1041', notify='283:1870';
const target=[dash,apt,lease,bills,pays,maint,profile]; let count=0;
const menus=[
 ['283:1913','283:1921','283:1927','283:1933','283:1939','283:1945','283:1950','283:1963'],
 ['282:1276','282:1284','282:1290','282:1296','282:1302','282:1308','282:1313','282:1326'],
 ['282:1385','282:1393','282:1399','282:1405','282:1411','282:1417','282:1422','282:1435'],
 ['282:1500','282:1508','282:1514','282:1520','282:1526','282:1532','282:1537','282:1550'],
 ['282:1610','282:1618','282:1624','282:1630','282:1636','282:1642','282:1647','282:1660']
];
for(const row of menus){for(let i=0;i<7;i++)if(await link(row[i],target[i]))count++;if(await link(row[7],login))count++;}
if(await link('277:1121',notify))count++;
if(await link('278:3982',dash))count++;
if(await link('283:1878',adminDash))count++;
if(await link('283:1897',adminDash))count++;
return {count};

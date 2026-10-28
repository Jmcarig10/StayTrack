const get = async (id) => await figma.getNodeByIdAsync(id);
const link = async (from, toId) => { const to=await get(toId); if(!from||!to)return false; await from.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:to.id,navigation:'NAVIGATE',transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_AND_OUT'}}}]}]); return true; };
const text = (root,value) => { const t=root.findAll((n)=>n.type==='TEXT'&&n.characters===value)[0]||null; return t ? t.parent : null; };
const tenantDash='283:1899', apartment='282:1262', lease='282:1371', bills='282:1486', payments='282:1596', maintenance='278:3503', profile='278:3761', login='278:3890', adminDash='277:1041', notifications='283:1870';
const frames=[tenantDash,apartment,lease,bills,payments];
const map={ 'My dashboard':tenantDash,'My apartment':apartment,'My lease':lease,'My bills':bills,'Payment history':payments,'Maintenance':maintenance,'Profile':profile,'Sign out':login };
let count=0;
for(const id of frames){const root=await get(id);for(const [label,target] of Object.entries(map)){if(await link(text(root,label),target))count++;}}
const bell=await get('277:1122'); if(await link(bell.parent,notifications))count++;
const register=await get('278:3982'); if(await link(register,tenantDash))count++;
const panel=await get(notifications); if(await link(text(panel,'Review your latest activity'),adminDash))count++; if(await link(text(panel,'Mark all as read'),adminDash))count++;
return {count};

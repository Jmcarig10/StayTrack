const get=async id=>await figma.getNodeByIdAsync(id);
const nav=async(fromId,toId)=>{const from=await get(fromId),to=await get(toId);if(!from||!to)return false;try{await from.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:to.id,navigation:'NAVIGATE',transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_AND_OUT'}}}]}]);return true;}catch{return false;}};
const close=async id=>{const n=await get(id);if(!n)return false;try{await n.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'CLOSE'}]}]);return true;}catch{return false;}};
let navCount=0,closeCount=0;
// Tenant logout controls across every Flow 4 screen.
for(const id of ['166:602','166:715','166:804','170:893','170:987','170:1419','170:1086','219:2814','219:2817','219:2965','219:2968'])if(await nav(id,'272:349'))navCount++;
// Header profile and notifications in the maintenance form/detail screens.
for(const id of ['219:2828','219:2979'])if(await nav(id,'170:1037'))navCount++;
for(const id of ['219:2824','219:2975'])if(await nav(id,'170:1357'))navCount++;
// Maintenance form actions follow the real route behavior.
for(const [a,b] of [['219:2894','170:1368'],['219:2896','170:1368']])if(await nav(a,b))navCount++;
// Existing screens have no separate download/saved states; keep the action connected to its owning screen.
for(const [a,b] of [['166:842','166:755'],['170:1133','170:1037']])if(await nav(a,b))navCount++;
// Duplicate notification panel close control uses a prototype CLOSE action.
if(await close('170:1294'))closeCount++;
return {navCount,closeCount};

const get=async id=>await figma.getNodeByIdAsync(id);
const nav=async(fromId,toId,mode='NAVIGATE')=>{const from=await get(fromId),to=await get(toId);if(!from||!to)return {id:fromId,ok:false,reason:'missing'};try{await from.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:to.id,navigation:mode,...(mode==='NAVIGATE'?{transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_AND_OUT'}}}:{})}]}]);return {id:fromId,to:toId,mode,ok:true};}catch(e){return {id:fromId,to:toId,mode,ok:false,reason:String(e).slice(0,300)}}};
const out=[];
// Every Tenant logout icon/text routes to the canonical login frame.
for(const id of ['166:600','166:602','166:713','166:715','166:802','166:804','170:891','170:893','170:985','170:987','170:1417','170:1419','170:1084','170:1086','219:2814','219:2815','219:2817','219:2965','219:2966','219:2968'])out.push(await nav(id,'275:799'));
// Maintenance form/detail notification buttons open the existing notification overlay.
for(const id of ['219:2824','219:2825','219:2975','219:2976'])out.push(await nav(id,'170:1357','OVERLAY'));
return out;

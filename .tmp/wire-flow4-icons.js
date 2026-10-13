const get=async id=>await figma.getNodeByIdAsync(id);
const nav=async(fromId,toId)=>{const from=await get(fromId),to=await get(toId);if(!from||!to)return {id:fromId,ok:false,reason:'missing'};try{await from.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:to.id,navigation:'NAVIGATE',transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_AND_OUT'}}}]}]);return {id:fromId,ok:true};}catch(e){return {id:fromId,ok:false,reason:String(e)}}};
const results=[];
for(const id of ['166:600','166:713','166:802','170:891','170:985','170:1417','170:1084','219:2815','219:2966'])results.push(await nav(id,'272:349'));
for(const id of ['219:2825','219:2976'])results.push(await nav(id,'170:1357'));
return results;

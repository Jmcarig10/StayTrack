const from=await figma.getNodeByIdAsync('219:2814');
const targets=['275:799','275:913','272:349'];
const out=[];
for(const id of targets){const to=await figma.getNodeByIdAsync(id);try{await from.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:to.id,navigation:'NAVIGATE'}]}]);out.push({id,ok:true});break}catch(e){out.push({id,ok:false,reason:String(e)})}}
return out;

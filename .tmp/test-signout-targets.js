const n=await figma.getNodeByIdAsync('166:600');const targets=['275:799','275:913','272:349','166:666'];const out=[];
for(const id of targets){const to=await figma.getNodeByIdAsync(id);try{await n.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:to.id,navigation:'NAVIGATE',transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_AND_OUT'}}}]}]);out.push({id,ok:true});break}catch(e){out.push({id,ok:false,reason:String(e).slice(0,260)})}}
return out;

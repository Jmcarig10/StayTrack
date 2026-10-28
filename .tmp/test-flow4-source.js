const ids=['219:2814','219:2815','219:2825','178:1454','166:600'];
const to=await figma.getNodeByIdAsync('166:553');const out=[];
for(const id of ids){const n=await figma.getNodeByIdAsync(id);try{await n.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:to.id,navigation:'NAVIGATE',transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_AND_OUT'}}}]}]);out.push({id,ok:true});}catch(e){out.push({id,ok:false,reason:String(e).slice(0,500)})}}
return out;

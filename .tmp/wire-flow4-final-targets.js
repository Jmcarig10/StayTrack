const get=async id=>await figma.getNodeByIdAsync(id);
const nav=async(fromId,toId)=>{const from=await get(fromId),to=await get(toId);try{await from.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:to.id,navigation:'NAVIGATE',transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_AND_OUT'}}}]}]);return {id:fromId,to:toId,ok:true};}catch(e){return {id:fromId,to:toId,ok:false,reason:String(e).slice(0,420)}}};
const out=[];
// Restore the maintenance CTA container and connect both its visible icon and button.
out.push(await nav('178:1454','219:2749'));
out.push(await nav('178:1455','219:2749'));
// Header notification controls in the two maintenance detail/form screens.
out.push(await nav('219:2825','170:1357'));
out.push(await nav('219:2976','170:1357'));
// Sign-out wrappers/icons that are valid prototype sources. Use the canonical login frame.
for(const id of ['219:2814','219:2815','219:2965','219:2966'])out.push(await nav(id,'275:799'));
return out;

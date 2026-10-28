const r=await figma.getNodeByIdAsync('284:5601');return {parent:{id:r.parent?.id,name:r.parent?.name,type:r.parent?.type},layoutMode:r.layoutMode,clips:r.clipsContent};

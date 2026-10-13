Set-Location 'C:\Users\Jmcarig\figma-cli'
$items = @(
  '<Frame name="NEW FLOW / Components / Button / Primary" w={160} h={42} bg="var:primary" rounded={8} flex="row" justify="center" items="center"><Text font="Figtree" size={13} weight="semibold" color="var:white">Primary action</Text></Frame>',
  '<Frame name="NEW FLOW / Components / Button / Secondary" w={160} h={42} bg="var:surface" stroke="var:border-strong" strokeWidth={1} rounded={8} flex="row" justify="center" items="center"><Text font="Figtree" size={13} weight="semibold" color="var:text">Secondary action</Text></Frame>',
  '<Frame name="NEW FLOW / Components / Input" w={280} h={44} bg="var:surface" stroke="var:border-strong" strokeWidth={1} rounded={8} flex="row" items="center" px={14}><Text font="Figtree" size={13} color="var:text-subtle">Input placeholder</Text></Frame>',
  '<Frame name="NEW FLOW / Components / Status badge" h={26} bg="var:emerald-50" rounded={9999} flex="row" items="center" px={10}><Text font="Figtree" size={11} weight="semibold" color="var:emerald-700">Active</Text></Frame>',
  '<Frame name="NEW FLOW / Components / Card" w={360} h={140} flex="col" gap={10} p={20} bg="var:surface" stroke="var:border" strokeWidth={1} rounded={12}><Text font="Figtree" size={16} weight="semibold" color="var:text-strong">Card title</Text><Text font="Figtree" size={13} color="var:text-muted">Reusable surface for StayTrack content.</Text></Frame>',
  '<Frame name="NEW FLOW / Components / Navigation item" w={220} h={38} bg="var:surface" rounded={8} flex="row" gap={10} items="center" px={12}><Icon name="lucide:building-2" size={17} color="var:text-muted"/><Text font="Figtree" size={13} color="var:text">Navigation item</Text></Frame>'
)
foreach ($jsx in $items) { $arg=$jsx -replace '"','\"'; node src/index.js render $arg --collection 'StayTrack Source' --as-component }

import { useState, useEffect } from 'react';
import { Alert } from 'react-native';
import SyncService from './src/services/syncService';
import SideMenu from './src/components/side_menu';
import CadastroUser from './src/pages/cadastro_user';
import Controller from './src/pages/controller';
import Registro from './src/pages/registro';
// pages components removed

export default function App() {
  const [page, setPage] = useState('cadastro');
  const [menuVisible, setMenuVisible] = useState(false);
  const navigate = (nextPage) => { setPage(nextPage); setMenuVisible(false); };
  const openMenu = () => setMenuVisible(true);
  const [selectedPageId, setSelectedPageId] = useState(null);

  useEffect(() => {
    // try to sync once on app start (non-blocking)
    (async () => {
      try {
        const res = await SyncService.syncAllStructured('remote_items');
        console.log('Structured sync completed', res);
      } catch (err) {
        console.log('Structured sync failed', err.message);
      }
    })();
    // pages sync removed per request
  }, []);

  return (
    <>
      {page === 'cadastro'
        ? <CadastroUser onBack={() => navigate('controle')} onMenu={openMenu} />
        : page === 'registro'
          ? <Registro onMenu={openMenu} />
          : <Controller onMenu={openMenu} />}
      <SideMenu activePage={page} visible={menuVisible} onClose={() => setMenuVisible(false)} onSelect={navigate} />
    </>
  );
}

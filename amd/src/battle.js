import Ajax from 'core/ajax';

const Selectors = {
    startBattleButton: '.tool_rpg-start-battle-button',
    declineBattleButton: '.tool_rpg-decline-battle-button',
    attackMonsterButton: '.tool_rpg-attack-monster-button',
    reloadButton: '.tool_rpg-reload-button'
};

const startBattle = (battleid) => {
    Ajax.call([{
        methodname: 'tool_rpg_start_battle',
        args: {battleid: battleid}
    }])[0].done(() => location.reload());
};

const declineBattle = (battleid) => {
    Ajax.call([{
        methodname: 'tool_rpg_decline_battle',
        args: {battleid: battleid}
    }])[0].done(() => {
        document.querySelector('.tool_rpg-start-battle')?.remove();
        document.querySelector('.tool_rpg-ongoing-battle')?.remove();
    });
};

const attackMonster = (battleid) => {
    Ajax.call([{
        methodname: 'tool_rpg_attack_monster',
        args: {battleid: battleid}
    }])[0].done(() => location.reload());
};

export const init = () => {
    document.addEventListener('click', event => {
        if (event.target.closest(Selectors.startBattleButton)) {
            if (event.target.dataset.battleid) {
                startBattle(event.target.dataset.battleid);
            }
        } else if (event.target.closest(Selectors.declineBattleButton)) {
            if (event.target.dataset.battleid) {
                declineBattle(event.target.dataset.battleid);
            }
        } else if (event.target.closest(Selectors.attackMonsterButton)) {
            if (event.target.dataset.battleid) {
                attackMonster(event.target.dataset.battleid);
            }
        } else if (event.target.closest(Selectors.reloadButton)) {
            location.reload();
        }
    });
};

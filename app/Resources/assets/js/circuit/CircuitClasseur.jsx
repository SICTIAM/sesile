import React, {Component} from 'react'
import PropTypes, {func} from 'prop-types'
import SearchUserAndGroup from '../_components/SearchUserAndGroup'

class CircuitClasseur extends Component {

    constructor(props) {
        super(props)
    }

    defaultProps = {
        editable: false
    }


    componentDidMount() {
        $(".add-actions-etapes").foundation()
    }

    componentDidUpdate() {
        $(".add-actions-etapes").foundation()
    }

    render () {

        const { etape_classeurs, user, addEtape, removeEtape, addUser, addGroup, editable, collectiviteId, removeUser, removeGroup } = this.props
        const { t } = this.context

        return (
            <div className="grid-x circuit-list">
                <div className="cell medium-12">

                    <div className="grid-x align-center-middle grid-margin-y">
                        <div className={ (editable ? "medium-1" : "medium-3") + " cell text-center"}>
                            <div className="circle success text-success">1</div>
                        </div>
                        <div className={ (editable ? "medium-2" : "medium-3") + " cell"}>
                            <span className="text-success">{t('admin.circuit.depositor')}</span>
                        </div>
                        <div className={ (editable ? "medium-6" : "medium-6") + " cell"}>
                            <span className="text-success text-bold">{user._prenom} {user._nom}</span>
                        </div>
                        { editable && <div className="cell medium-3"></div>}
                    </div>

                    { etape_classeurs && etape_classeurs.map((etape_classeur, key) =>
                        <div key={"etape_classeur-" + key} className="grid-x align-center-middle grid-margin-y">
                            <div className={ (editable ? "medium-1" : "medium-3") + " cell text-center"}>
                                <div className={ etape_classeur.etape_valide ? ("circle success") : ("circle gray") }>{key + 2}</div>
                            </div>
                            <div className={ (editable ? "medium-2" : "medium-3") + " cell"}>
                                <span className={ etape_classeur.etape_valide ? ("text-success text-bold") : ("text-gray text-bold")}>{t('admin.circuit.validator')}</span>
                            </div>
                            <div className={ (editable ? "medium-6" : "medium-6") + " cell"}>
                                <span className={ etape_classeur.etape_valide ? ("text-success text-bold") : ("text-gray text-bold") }>

                                { etape_classeur.users && etape_classeur.users.map((user, userKey) =>
                                    <div key={"user" + user.id}>
                                        {user._prenom} {user._nom} {
                                           editable && !etape_classeur.etape_valide &&
                                            <a onClick={() => removeUser(key, userKey)}><i className="fi-x"></i></a>
                                        }
                                    </div>
                                )}

                                {etape_classeur.user_packs && etape_classeur.user_packs.map((user_pack, user_packKey) =>
                                    <div key={"userpack" + user_pack.id}>
                                        {user_pack.nom} {
                                            editable && !etape_classeur.etape_valide
                                            && <a onClick={() => removeGroup(key, user_packKey)}><i className="fi-x"></i></a>
                                        }
                                    </div>
                                )}
                                </span>
                            </div>

                            {
                                editable &&
                                <div className="cell medium-3 text-right">
                                    { !etape_classeur.etape_valide &&
                                        <div>
                                            <button className="button clear primary add-actions-etapes" type="button" data-toggle={"add-actions-etapes" + key}>
                                                <i className="fi-plus"></i>

                                                <div className="dropdown-pane dropdown-add-actions text-left" data-position="bottom" data-alignment="left" id={"add-actions-etapes" + key} data-dropdown data-auto-focus="true">
                                                    <label><span className="dropdown-add-actions-text">{t('common.button.add_user')}</span>

                                                        <SearchUserAndGroup placeholder={t('admin.placeholder.type_userName_or_groupName')}
                                                                            addGroup={addGroup}
                                                                            addUser={addUser}
                                                                            stepKey={key}
                                                                            step={etape_classeur}
                                                                            collectiviteId={collectiviteId}
                                                        />

                                                    </label>
                                                    <a onClick={() => addEtape(key + 1)} className="dropdown-add-actions-text">
                                                        {t('admin.circuit.add_step')}
                                                    </a>
                                                </div>
                                            </button>
                                            <button type="button" className="button clear primary" onClick={() => removeEtape(key)} title={t('admin.circuit.remove_step')}>
                                                <i className="fi-minus"></i>
                                            </button>
                                        </div>
                                    }
                                </div>
                            }
                        </div>
                        )
                    }

                    {
                        editable &&
                        <div className="grid-x align-center-middle grid-padding-x">
                            <button type="button" className="button clear primary" onClick={() => addEtape(null)}>
                                <i className="fi-plus"> {t('admin.circuit.add_step')}</i>
                            </button>
                        </div>
                    }
                </div>
            </div>
        )
    }
}

CircuitClasseur.PropTypes = {
    classeurId: PropTypes.number,
    etape_classeurs: PropTypes.object.isRequired,
    user: PropTypes.object.isRequired,
    addEtape: PropTypes.func,
    removeEtape: PropTypes.func,
    removeUser: PropTypes.func,
    removeGroup: PropTypes.func,
    addGroup: PropTypes.func,
    addUser: PropTypes.func,
    editable: PropTypes.bool,
    collectiviteId: PropTypes.number
}

CircuitClasseur.contextTypes = {
    t: func
}

export default CircuitClasseur
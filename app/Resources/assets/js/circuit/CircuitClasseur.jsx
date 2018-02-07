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

    currentCircleClassName (etape_classeur) {
        if(etape_classeur.etape_valide) {
            return "success text-success"
        } else if(etape_classeur.etape_validante) {
            return "warning text-warning"
        } else {
            return "gray text-gray"
        }
    }

    currentTextClassName (etape_classeur) {
        if(etape_classeur.etape_valide) {
            return "text-success"
        } else if(etape_classeur.etape_validante) {
            return "text-warning"
        } else {
            return "text-gray"
        }
    }

    render () {

        const { etape_classeurs, user, etapeDeposante, addEtape, removeEtape, addUser, addGroup, editable, collectiviteId, removeUser, removeGroup } = this.props
        const { t } = this.context

        return (
            <div className="grid-x circuit-list">
                <div className="cell medium-12">

                    <div className="grid-x align-top grid-margin-y">
                        <div className="medium-2 cell text-center">
                            <div className={ etapeDeposante ? ("circle warning text-warning") : ("circle success text-success") }>1</div>
                        </div>
                        <div className="medium-3 cell">
                            <span className={ etapeDeposante ? ("text-warning text-bold") : ("text-success text-bold")}>{t('admin.circuit.depositor')}</span>
                        </div>
                        <div className="medium-7 cell">
                            <span className={ etapeDeposante ? ("text-warning text-bold") : ("text-success text-bold") }>{user._prenom} {user._nom}</span>
                        </div>
                    </div>

                    { etape_classeurs && etape_classeurs.map((etape_classeur, key) =>
                        <div key={"etape_classeur-" + key} >
                            <div className="grid-x align-top grid-margin-y">
                                <div className="medium-2 cell text-center">
                                    <div className={ this.currentCircleClassName(etape_classeur) + " circle" }>{key + 2}</div>
                                </div>
                                <div className="medium-3 cell padding-cell">
                                    <span className={ this.currentTextClassName (etape_classeur) + " text-bold" }>{t('admin.circuit.validator')}</span>
                                </div>
                                <div className="medium-7 cell padding-cell">
                                    <span className={ this.currentTextClassName (etape_classeur) + " text-bold"  }>

                                    { etape_classeur.users && etape_classeur.users.filter(user => user.id).map((user, userKey) =>
                                        <div key={"user" + user.id}>
                                            {user._prenom} {user._nom} {
                                               editable && !etape_classeur.etape_valide && !etape_classeur.etape_validante &&
                                                <a onClick={() => removeUser(key, userKey)}><i className="fa fa-times icon-action"></i></a>
                                            }
                                        </div>
                                    )}

                                    {etape_classeur.user_packs && etape_classeur.user_packs.map((user_pack, user_packKey) =>
                                        <div key={"userpack" + user_pack.id}>
                                            {user_pack.nom} {
                                                editable && !etape_classeur.etape_valide && !etape_classeur.etape_validante &&
                                                <a onClick={() => removeGroup(key, user_packKey)}><i className="fa fa-times icon-action"></i></a>
                                            }
                                        </div>
                                    )}
                                    </span>
                                </div>
                            </div>

                            { editable && !etape_classeur.etape_valide && !etape_classeur.etape_validante &&
                            <div className="grid-x align-top">

                                <div className="cell medium-2"></div>
                                <div className="cell medium-3">
                                    <button type="button" className="button clear primary" onClick={() => removeEtape(key)} title={t('admin.circuit.remove_step')}>
                                        <i className="fa fa-minus icon-action"></i>
                                    </button>
                                </div>
                                <div className="cell medium-7 text-center">
                                    <SearchUserAndGroup placeholder={t('admin.placeholder.type_userName_or_groupName')}
                                                        addGroup={addGroup}
                                                        addUser={addUser}
                                                        stepKey={key}
                                                        step={etape_classeur}
                                                        collectiviteId={collectiviteId}
                                    />
                                </div>
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
    etapeDeposante: PropTypes.number,
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
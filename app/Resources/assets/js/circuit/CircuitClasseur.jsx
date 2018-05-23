import React, {Component} from 'react'
import PropTypes, {func} from 'prop-types'

import { Button } from '../_components/Form'
import SearchUserAndGroup from '../_components/SearchUserAndGroup'

class CircuitClasseur extends Component {
    defaultProps = {
        editable: false
    }
    componentDidMount() {
        $(".add-actions-etapes").foundation()
    }
    componentDidUpdate() {
        $(".add-actions-etapes").foundation()
    }
    currentCircleClassName = (etape_classeur) => {
        if(etape_classeur.etape_valide) {
            return "success text-success"
        } else if(etape_classeur.etape_validante) {
            return "warning text-warning"
        } else {
            return "gray text-gray"
        }
    }
    currentTextClassName = (etape_classeur) => {
        if(etape_classeur.etape_valide) {
            return "text-success"
        } else if(etape_classeur.etape_validante) {
            return "text-warning"
        } else {
            return "text-gray"
        }
    }
    isLastStep = (etape_classeur) => {
        return this.props.editable && !etape_classeur.etape_valide && !etape_classeur.etape_validante
    }
    saveCircuit = () => {
        const etapeClasseurs = this.props.etape_classeurs
        Object.assign(etapeClasseurs, this.props.etape_classeurs.map(etape_classeur => { return {
            ordre: etape_classeur.ordre,
            users: etape_classeur.users.map(user => user.id),
            user_packs: etape_classeur.user_packs.map(user_pack => user_pack.id),
        }}))
        const fields = {
            etapeClasseurs: etapeClasseurs
        }
        this.props.putClasseur(fields)
    }
    render () {
        const { t } = this.context
        const stepsCircuit = this.props.etape_classeurs.map((etape_classeur, key) =>
            <StepCircuit
                key={key}
                stepKey={key}
                etape_classeur={etape_classeur}
                currentCircleClassName={this.currentCircleClassName}
                isLastStep={this.isLastStep}
                currentTextClassName={this.currentTextClassName}
                removeEtape={this.props.removeEtape}
                removeUser={this.props.removeUser}
                removeGroup={this.props.removeGroup}
                addGroup={this.props.addGroup}
                addUser={this.props.addUser}
                editable={this.props.editable}
                collectiviteId={this.props.collectiviteId}/>)

        return (
            <div className="grid-x panel grid-padding-y">
                <div className="cell small-12 medium-12 large-12">
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <h3 className="cell small-12 medium-12 large-12">
                            {t('admin.circuit.complet_name')}
                        </h3>
                    </div>
                    <div className="grid-x grid-margin-x grid-padding-x circuit-list">
                        <div className="cell small-12 medium-12 large-12">
                            <div
                                className={
                                    `align-middle
                                    ${this.props.etapeDeposante ?
                                        ("text-warning") :
                                        ("text-success")}`}
                                style={{
                                    marginBottom: '10px',
                                    width: '100%',
                                    minHeight: '5em',
                                    display: 'flex',
                                    boxShadow: 'rgba(34, 36, 38, 0.15) 0px 1px 2px 0px',
                                    borderRadius: '0.285714rem',
                                    border: '1px solid',
                                    padding: '0.5em'}}>
                                <div
                                    className="text-center"
                                    style={{display: 'inline-block', width: '2.5rem'}}>
                                    <div
                                        className={
                                            this.props.etapeDeposante ?
                                                ("circle warning text-warning") :
                                                ("circle success text-success")}>
                                        1
                                    </div>
                                </div>
                                <div
                                    className="text-uppercase"
                                    style={{display: 'inline-block', width: '7rem', margin: '5px'}}>
                                    <span
                                        className={
                                            this.props.etapeDeposante ?
                                                ("text-warning text-bold") :
                                                ("text-success text-bold")}>
                                        {t('admin.circuit.depositor')}
                                    </span>
                                </div>
                                <div className="" style={{width: '65%'}}>
                                    <span
                                        className={
                                            this.props.etapeDeposante ?
                                                ("text-warning text-bold") :
                                                ("text-success text-bold") }>
                                        {this.props.user._prenom} {this.props.user._nom}
                                    </span>
                                </div>
                            </div>
                            {stepsCircuit}
                            {this.props.editable &&
                                <div
                                    className="align-center align-middle text-bold"
                                    style={{
                                        color: '#34a3fc',
                                        fontSize: '1.2em',
                                        marginBottom: '20px',
                                        width: '100%',
                                        display: 'flex',
                                        boxShadow: 'rgba(34, 36, 38, 0.15) 0px 1px 2px 0px',
                                        borderRadius: '0.285714rem',
                                        border: '1px solid #34a3fc',
                                        padding: '0.5em',
                                        cursor: 'pointer'}}
                                    onClick={() => this.props.addEtape(null)}>
                                    <i className="fa fa-plus-circle hollow primary text-bold"/>
                                    <span style={{marginLeft: '0.5em'}}>
                                        {t('admin.circuit.add_step')}
                                    </span>
                                </div>}
                        </div>
                    </div>
                    <div className="grid-x grid-margin-x grid-padding-x align-right">
                        <Button
                            id="submit-classeur-infos"
                            className="cell small-6 medium-8"
                            classNameButton="float-right"
                            onClick={this.saveCircuit}
                            labelText={t('common.button.edit_save')}/>
                    </div>
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

const StepCircuit = ({
    stepKey,
    etape_classeur,
    currentCircleClassName,
    isLastStep,
    currentTextClassName,
    removeEtape,
    removeUser,
    removeGroup,
    addGroup,
    addUser,
    editable,
    collectiviteId}, {t}) => {
    return (
        <div
            className={`align-middle ${currentCircleClassName(etape_classeur)}`}
            style={{
                marginBottom: '10px',
                width: '100%',
                minHeight: '5em',
                display: 'flex',
                boxShadow: 'rgba(34, 36, 38, 0.15) 0px 1px 2px 0px',
                borderRadius: '0.285714rem',
                border: '1px solid',
                padding: '0.5em'}}>
            <div className="text-center" style={{display: 'inline-block', width: '2.5rem'}}>
                <div className={ currentCircleClassName(etape_classeur) + " circle" }>
                    {stepKey + 2}
                </div>
            </div>
            <div style={{display: 'inline-block', width: '7rem', margin: '5px'}}>
                <span
                    className={`${currentTextClassName(etape_classeur)} text-uppercase text-bold`}>
                    {t('admin.circuit.validator')}
                </span>
            </div>
            <div
                className="align-right"
                style={{
                    width: `${isLastStep(etape_classeur) ? '60%' : '65%'}`,
                    marginTop:`${isLastStep(etape_classeur) ? '1.5em' : '0em'}`}}>
                <div className={`${currentTextClassName(etape_classeur)} text-bold`}>
                    {etape_classeur.users && etape_classeur.users.filter(user => user.id).map((user, userKey) =>
                        <div key={"user" + user.id} style={{display: 'inline-block', width: '100%'}}>
                            <div style={{display: 'inline-block', width: '89%'}}>
                                {user._prenom} {user._nom}
                            </div>
                            {editable && !etape_classeur.etape_valide && !etape_classeur.etape_validante &&
                            <div style={{display: 'inline-block', float: 'right', width: '11%'}}>
                                <a onClick={() => removeUser(stepKey, userKey)}>
                                    <i className="fa fa-times icon-action" style={{fontSize: '1.2em'}}></i>
                                </a>
                            </div>}
                        </div>)}
                    {etape_classeur.user_packs && etape_classeur.user_packs.map((user_pack, user_packKey) =>
                        <div key={"userpack" + user_pack.id} style={{display: 'inline-block', width: '100%'}}>
                            <div style={{display: 'inline-block', width: '89%'}}>
                                {user_pack.nom}
                            </div>
                            {editable && !etape_classeur.etape_valide && !etape_classeur.etape_validante &&
                            <div style={{display: 'inline-block', float: 'right', width: '11%'}}>
                                <a onClick={() => removeGroup(stepKey, user_packKey)}>
                                    <i className="fa fa-times icon-action" style={{fontSize: '1.2em'}}></i>
                                </a>
                            </div>}
                        </div>)}
                </div>
                {isLastStep(etape_classeur) &&
                <div style={{display: 'inline-block', width: '100%'}}>
                    <SearchUserAndGroup
                        placeholder={t('admin.placeholder.type_userName_or_groupName')}
                        addGroup={addGroup}
                        addUser={addUser}
                        stepKey={stepKey}
                        step={etape_classeur}
                        collectiviteId={collectiviteId}/>
                </div>}
            </div>
            {isLastStep(etape_classeur) &&
            <div className="align-self-top" style={{display: 'inline-block', width: '1rem'}}>
                <i  className="fa fa-times-circle icon-action"
                    title={t('admin.circuit.remove_step')}
                    onClick={() => removeEtape(stepKey)}>
                </i>
            </div>}
        </div>
    )
}

StepCircuit.contextTypes = {
    t: func
}
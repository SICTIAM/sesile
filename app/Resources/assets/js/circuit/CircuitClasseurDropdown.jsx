import React, {Component} from 'react'
import { func } from 'prop-types'

class CircuitClasseurDropdown extends Component {
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
                currentTextClassName={this.currentTextClassName}/>)

        return (
            <div className="grid-x grid-margin-x circuit-list">
                <div className="cell medium-12">
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
                            style={{display: 'inline-block', width: '10%'}}>
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
                            style={{display: 'inline-block', width: '30%', margin: '5px'}}>
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
                </div>
            </div>
        )
    }
}

CircuitClasseurDropdown.contextTypes = {
    t: func
}

export default CircuitClasseurDropdown

const StepCircuit = ({
                         stepKey,
                         etape_classeur,
                         currentCircleClassName,
                         isLastStep,
                         currentTextClassName}, {t}) => {
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
            <div className="text-center" style={{display: 'inline-block', width: '10%'}}>
                <div className={ currentCircleClassName(etape_classeur) + " circle" }>
                    {stepKey + 2}
                </div>
            </div>
            <div style={{display: 'inline-block', width: '30%', margin: '5px'}}>
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
                            <div style={{display: 'inline-block', width: '90%'}}>
                                {user._prenom} {user._nom}
                            </div>
                        </div>)}
                    {etape_classeur.user_packs && etape_classeur.user_packs.map((user_pack, user_packKey) =>
                        <div key={"userpack" + user_pack.id} style={{display: 'inline-block', width: '100%'}}>
                            <div style={{display: 'inline-block', width: '90%'}}>
                                {user_pack.nom}
                            </div>
                        </div>)}
                </div>
            </div>
        </div>
    )
}

StepCircuit.contextTypes = {
    t: func
}
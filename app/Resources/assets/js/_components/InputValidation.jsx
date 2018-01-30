import React, { Component } from 'react'
import Validator from 'validatorjs'
import { Input, InputDatePicker } from './Form'

export default class InputValidation extends Component {
    state = { isValid: true, errorMessage: '' }
    static defaultProps = { value: '', type: '', accept: '', className: '', autoFocus: false, labelText: '', minDate: '', maxDate: ''  }
    validateValue = () => {
        const validation = new Validator({ [this.props.id]: this.props.value }, { [this.props.id]: this.props.validationRule }, this.props.customErrorMessages)
        const isValid = validation.passes()
        const errorMessage = validation.errors.first(this.props.id)
        this.setState({ isValid: isValid, errorMessage: errorMessage })
    }
    render() {
        return (
            <div className={this.props.className}>
                {(this.props.type === 'text' || '' || undefined) &&
                    <Input  id={this.props.id}
                            autoFocus={this.props.autoFocus}
                            labelText={this.props.labelText}
                            type={this.props.type}
                            className={this.props.className}
                            placeholder={this.props.placeholder}
                            value={this.props.value}
                            onChange={this.props.onChange}
                            onBlur={this.validateValue}
                            helpText={this.props.helpText}/>}
                {(this.props.type === 'date' ) &&
                    <InputDatePicker    id={this.props.id}
                                        date={this.props.value}
                                        label={this.props.labelText}
                                        locale={this.props.locale}
                                        readOnly={this.props.readOnly}
                                        minDate={this.props.minDate}
                                        onBlur={this.validateValue}
                                        onChange={this.props.onChange}/>}
                {(!this.state.isValid || this.props.isValid) && 
                    (<span style={{color:"red"}} className="text-small">
                        {this.state.errorMessage || this.props.errorMessage}
                    </span>)}
            </div>
        )
    }
}
